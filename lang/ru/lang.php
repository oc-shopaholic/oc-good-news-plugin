<?php return [
    'plugin'    => [
        'name'        => 'GoodNews',
        'description' => 'Новостной плагин',
    ],
    'field'     => [
        'content'                                => 'Контент новости',
        'published_start'                        => 'Дата начала публикации',
        'published_stop'                         => 'Дата окончания публикации',
        'status'                                 => 'Статус',
        'seo_title'                              => 'Заголовок (meta title)',
        'seo_keywords'                           => 'Ключевые слова (meta keywords)',
        'seo_description'                        => 'Описание (meta description)',

        'is_hidden_additional_fields_fileupload' => 'Скрыть дополнительные поля изображений',
    ],
    'component' => [
        'article_page'         => 'Страница новости',
        'article_page_desc'    => '',
        'article_data'         => 'Данные новости',
        'article_data_desc'    => '',
        'article_list'         => 'Список новостей',
        'article_list_desc'    => '',

        'category_page'        => 'Страница категории',
        'category_page_desc'   => '',
        'category_data'        => 'Данные категории',
        'category_data_desc'   => '',
        'category_list'        => 'Список категорий',
        'category_list_desc'   => '',

        'sorting_publish_asc'     => 'По дате публикации (asc)',
        'sorting_publish_desc'    => 'По дате публикации (desc)',
        'sorting_view_count_acs'  => 'По количеству просмотров (asc)',
        'sorting_view_count_desc' => 'По количеству просмотров (desc)',
    ],
    'menu'      => [
        'article'  => 'Новости',
        'category' => 'Категории',
    ],
    'tab'       => [
        'permissions' => 'Управление блонком новостей',
        'meta'        => 'Метатеги',
        'settings'    => 'Настройки GoodNews',
    ],
    'article'   => [
        'name'       => 'новости',
        'list_title' => 'Список новостей',
    ],
    'category'  => [
        'name'       => 'категории',
        'list_title' => 'Список категорий',
    ],
    'status' => [
        1 => 'Новая',
        2 => 'Готовится к выпуску',
        3 => 'Проверяется',
        4 => 'Опубликована',
    ],
    'permission' => [
        'article'  => 'Управление новостями',
        'category' => 'Управление категориями',
    ],
];
