## Components

### ArticlePage component

The component allows to work with ArticleItem class objects.

The ArticlePage class is extended from [ElementPage](https://github.com/lovata/oc-toolbox-plugin/wiki/ElementPage) class.

Usage example:
```twig
[ArticlePage]
slug = "{{ :slug }}"
slug_required = 1
==

{# Get article item #}
{% set obArticle = ArticlePage.get() %}
<div data-id="{{ obArticle.id }}">
    <h1>{{ obArticle.title }}</h1>
    <span>{{ obArticle.published_start.format('F d, Y') }}</span>
    {% if obArticle.preview_image is not empty %}
        <img src="{{ obArticle.preview_image.path }}" title="{{ obArticle.preview_image.title }}" alt="{{ obArticle.preview_image.description }}">
    {% endif %}
    <div>{{ obArticle.description|raw }}</div>
</div>
```

### ArticleData component
  
The component allows to work with ArticleItem class objects.
  
The ArticleData class is extended from [ElementData](https://github.com/lovata/oc-toolbox-plugin/wiki/ElementData) class.
  
Usage example:
```twig

{# Get article item with ID = 10 #}
{% set obArticle = ArticleData.get(10) %}
{% if obArticle.isNotEmpty() %}
    <div data-id="{{ obArticle.id }}">
        <h2>{{ obArticle.title }}</h2>
        <span>{{ obArticle.published_start.format('F d, Y') }}</span>
        {% if obArticle.preview_image is not empty %}
            <img src="{{ obArticle.preview_image.path }}" title="{{ obArticle.preview_image.title }}" alt="{{ obArticle.preview_image.description }}">
        {% endif %}
        <div>{{ obArticle.description|raw }}</div>
    </div>
{% endif %}
```

### ArticleList component

The component allows to work with ArticleCollection class objects.

#### Method list

##### make($arElementIDList = null)
**Example: render article list**

Get collection of articles, apply sorting + filter by published status
```twig

{% set obArticleList = ArticleList.make().sort('publish|desc').published() %}
{% if obArticleList.isNotEmpty() %}
    <div class="article-list-wrapper">
        {% for obArticle in obArticleList %}
            <div data-id="{{ obArticle.id }}">
                <h2>{{ obArticle.title }}</h2>
                <span>{{ obArticle.published_start.format('F d, Y') }}</span>
                {% if obArticle.preview_image is not empty %}
                    <img src="{{ obArticle.preview_image.path }}" title="{{ obArticle.preview_image.title }}" alt="{{ obArticle.preview_image.description }}">
                {% endif %}
                <div>{{ obArticle.preview_text }}</div>
            </div>
        {% endfor %}
    </div>
{% endif %}
```

Get article collection, apply sorting + filter by published status + [Pagination](https://github.com/lovata/oc-toolbox-plugin/wiki/Components#pagination) component
```twig

{# Get article collection #}
{% set obArticleList = ArticleList.make().sort('publish|desc').published() %}

{# Get array with pagination buttons #}
{% set iPage = Pagination.getPageFromRequest() %}
{% set arPaginationList = Pagination.get(iPage, obArticleList.count()) %}

{# Apply pagination to article collection and get array with article items #}
{% set arArticleList = obArticleList.page(iPage, Pagination.getCountPerPage()) %}

{% if arArticleList is not empty %}

    {# Render article list #}
    <div class="article-list-wrapper">
        {% for obArticle in obArticleList %}
            <div data-id="{{ obArticle.id }}">
                <h2>{{ obArticle.title }}</h2>
                <span>{{ obArticle.published_start.format('F d, Y') }}</span>
                {% if obArticle.preview_image is not empty %}
                    <img src="{{ obArticle.preview_image.path }}" title="{{ obArticle.preview_image.title }}" alt="{{ obArticle.preview_image.description }}">
                {% endif %}
                <div>{{ obArticle.preview_text }}</div>
            </div>
        {% endfor %}
    </div>
    
    {# Render pagination buttons #}
    {% if arPaginationList is not empty %}
        {% for arPagination in arPaginationList %}
            <a href="/{{ arPagination.value }}" class="{{ arPagination.class }}">{{ arPagination.name }}</a>
        {% endfor %}
    {% endif %}
{% endif %}
```

### ArticleCategoryPage component

The component allows to work with CategoryItem class objects.

The ArticleCategoryPage class is extended from [ElementPage](https://github.com/lovata/oc-toolbox-plugin/wiki/ElementPage) class.

Usage example:
```twig
[ArticleCategoryPage]
slug = "{{ :slug }}"
slug_required = 1
==

{# Get category item #}
{% set obCategory = ArticleCategoryPage.get() %}
<div data-id="{{ obCategory.id }}">
    <h1>{{ obCategory.name }}</h1>
    {% if obCategory.preview_image is not empty %}
        <img src="{{ obCategory.preview_image.path }}" title="{{ obCategory.preview_image.title }}" alt="{{ obCategory.preview_image.description }}">
    {% endif %}
    <div>{{ obCategory.description|raw }}</div>
    {% if obCategory.children.isNotEmpty() %}
        <ul>
            {% for obChildCategory in obCategory.children %}
                <li>{{ obChildCategory.name }}</li>
            {% endfor %}
        </ul>
    {% endif %}
</div>
```

### ArticleCategoryData component

The component allows to work with CategoryItem class objects.

The ArticleCategoryData class is extended from [ElementData](https://github.com/lovata/oc-toolbox-plugin/wiki/ElementData) class.

Usage example:
```twig

{# Get category item with ID = 10 #}
{% set obCategory = ArticleCategoryData.get(10) %}
{% if obCategory.isNotEmpty() %}
    <div data-id="{{ obCategory.id }}">
        <h2>{{ obCategory.name }}</h2>
        {% if obCategory.preview_image is not empty %}
            <img src="{{ obCategory.preview_image.path }}" title="{{ obCategory.preview_image.title }}" alt="{{ obCategory.preview_image.description }}">
        {% endif %}
        <div>{{ obCategory.description|raw }}</div>
        {% if obCategory.children.isNotEmpty() %}
            <ul>
                {% for obChildCategory in obCategory.children %}
                    <li>{{ obChildCategory.name }}</li>
                {% endfor %}
            </ul>
        {% endif %}
    </div>
{% endif %}
```

### ArticleCategoryList component

The component allows to work with CategoryCollection class objects.

#### Method list

##### make($arElementIDList = null)
**Example: render of category list**

Get tree of categories. Example is used in render of category menu.
```twig

{% set obCategoryList = ArticleCategoryList.make().tree() %}
{% if obCategoryList.isNotEmpty() %}
    <ul class="category-menu-wrapper">
        {% for obCategory in obCategoryList %}
            <li data-id="{{ obCategory.id }}">{{ obCategory.name }}
                {% if obCategory.children.isNotEmpty() %}
                    <ul class="category-child-menu-wrapper">
                        {% for obChildCategory in obCategory.children %}
                            <li>{{ obChildCategory.name }}</li>
                        {% endfor %}
                    </ul>
                {% endif %}
            </li>
        {% endfor %}
    </ul>
{% endif %}
```

### ArticleCollection class

The class allows to work with a cached list of articles.

The ArticleCollection class is extended from [ElementCollection](https://github.com/lovata/oc-toolbox-plugin/wiki/ElementCollection) class.

The ArticleCollection class returns arrays of  [ArticleItem](https://github.com/lovata/oc-shopaholic-plugin/wiki/ArticleItem) class objects.

#### Method List:
##### sort($sSorting)

Method sorts the elements of a collection by $sSorting value.
Available sorting value:
  * 'no' - default value
  * 'publish|asc'
  * 'publish|desc'
  * 'view|asc'
  * 'view|desc'
```php
    $obList = ArticleCollection::make([1,2,10,15])->sort('publish|desc');
```

##### published()

Method applies a filter by publish status and publish date for the elements of a collection
```php
    $obList = ArticleCollection::make([1,2,10,15])->published();
```

##### category($iCategoryID, $bWithChildren = false)
  * $iCategoryID - article category ID or array with ID of categories
  * $bWithChildren - flag, if == true, then method returns list of articles for category with ID == $iCategoryID + articles of children categories 

Method applies a filter by the category ID.
```php
    $obList = ArticleCollection::make()->category(2);
```
```php
    $obList = ArticleCollection::make()->category(2, true);
```
```php
    $obList = ArticleCollection::make()->category([2, 5, 6]);
```
```php
    $obList = ArticleCollection::make()->category([2, 5, 6], true);
```

### CategoryCollection class

The class allows to work with a cached list of categories.

The CategoryCollection class is extended from [ElementCollection](https://github.com/lovata/oc-toolbox-plugin/wiki/ElementCollection) class.

The CategoryCollection class returns arrays of CategoryItem class objects.

#### Method List:
##### tree()

Method returns category list of top level. Method returns only active categories.
```php
    $obList = CategoryCollection::make()->tree();
```

##### active()

Method applies a filter to the field "active" = true  for the elements of a collection.
```php
    $obList = CategoryCollection::make([1,2,10,15])->active();
```

### ArticleItem class

The class allows to work with a cached data array of Article model.

The ArticleItem class is extended from [ElementItem](https://github.com/lovata/oc-toolbox-plugin/wiki/ElementItem) class.

## Field list
  * (int) **id**
  * (string) **status_id**
  * (string) **title**
  * (string) **slug**
  * (\October\Rain\Argon\Argon) **published_start**
  * (\October\Rain\Argon\Argon) **published_stop**
  * (\October\Rain\Argon\Argon) **created_at**
  * (\October\Rain\Argon\Argon) **updated_at**
  * (string) **preview_text**
  * \System\Models\File **preview_image**
  * (string) **content**
  * \System\Models\File[] **images**
  * (int) **view_count**
  * (int) **category_id**
  * CategoryItem **category** - object of parent category

### CategoryItem class

The class allows to work with a cached data array of Category model.

The CategoryItem class is extended from [ElementItem](https://github.com/lovata/oc-toolbox-plugin/wiki/ElementItem) class.

## Field list
  * (int) **id**
  * (string) **name**
  * (string) **slug**
  * (string) **code**
  * (string) **preview_text**
  * \System\Models\File **preview_image**
  * (string) **description**
  * \System\Models\File[] **images**
  * (int) **nest_depth**
  * (int) **parent_id**
  * CategoryItem **parent** - object of parent category
  * (array) **children_id_list** - array with **active** children category ID list
  * CategoryCollection **children** - collection with **active** children category

## License

© 2019, [LOVATA Group, LLC](https://github.com/lovata) under [GNU GPL v3](https://opensource.org/licenses/GPL-3.0).

Developed by [Andrey Kharanenka](https://github.com/kharanenka).