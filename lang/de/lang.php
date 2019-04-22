<?php return [
    'plugin'    => [
        'name'        => 'GoodNews',
        'description' => 'Artikel-Plugin',
    ],
    'field'     => [
        'content'         => 'Inhalt',
        'published_start' => 'Veröffentlichungsdatum',
        'published_stop'  => 'Ende der Veröffentlichung',
        'status'          => 'Status',
    ],
    'component' => [
        'article_page'         => 'Artikelseite',
        'article_page_desc'    => '',
        'article_data'         => 'Artikeldaten',
        'article_data_desc'    => '',
        'article_list'         => 'Artikelliste',
        'article_list_desc'    => '',

        'category_page'        => 'Kategorieseite',
        'category_page_desc'   => '',
        'category_data'        => 'Kategoriedaten',
        'category_data_desc'   => '',
        'category_list'        => 'Kategorieliste',
        'category_list_desc'   => '',
        
        'sorting_publish_asc'     => 'By date of publication (asc)',
        'sorting_publish_desc'    => 'By date of publication (desc)',
        'sorting_view_count_acs'  => 'By view count (asc)',
        'sorting_view_count_desc' => 'By view count (desc)',
    ],
    'menu'      => [
        'article'  => 'Articles',
        'category' => 'Categories',
    ],
    'tab'       => [
        'permissions' => 'Manage article',
    ],
    'article'   => [
        'name'       => 'article',
        'list_title' => 'Article list',
    ],
    'category'  => [
        'name'       => 'category',
        'list_title' => 'Category list',
    ],
    'status' => [
        1 => 'New',
        2 => 'In progress',
        3 => 'Checking',
        4 => 'Published',
    ],
    'permission' => [
        'article'  => 'Manage article',
        'category' => 'Manage category',
    ],
];