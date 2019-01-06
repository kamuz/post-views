# Количество просмотров статей

Создадим файл с метаданными и пока что просто создадим отдельное поле для учёта количества кликов в таблице `wp_posts`:

*wp-content/plugins/kmz-post-views/kmz-post-views.php*

```php
<?php

/*
Plugin Name: KMZ Page Views
Description: Simple plugin for count post views
Plugin URI: https://wpdev.pp.ua
Author: Vladimir Kamuz
Author URI: https://wpdev.pp.ua
Text Domain: kmz-post-views
Version: 1.0.0
*/

register_activation_hook(__FILE__, 'kmz_post_create_field');

function kmz_post_create_field(){
    global $wpdb;

    $query = "ALTER TABLE $wpdb->posts ADD post_views INT NOT NULL DEFAULT '0'";
    $wpdb->query($query);
}
```

Для создания поля мы используем глобальный объект `$wpdb` у которого вызываем метод `query()`, который выполнит необходимый нам SQL запрос. Чтобы быть увереным что наш запрос отработает с необходимым префиксом БД для WordPress нам нужно название префикса задавать динамическим через `$wpdb->posts`.

Для функции удаления мы создадим отдельный файл:

*wp-content/plugins/kmz-post-views/uninstall.php*

```php
<?php

if( !defined('WP_UNINSTALL_PLUGIN') ) exit;

global $wpdb;

$query = "ALTER TABLE $wpdb->posts DROP post_views";
$wpdb->query($query);
```

В начале мы проверяем если не опредена ли константа `UNINSTALL_PLUGIN` тогда мы завершаем работу с данным файлом.

Для вывода количества просмотров мы будем использовать фильтр `the_content` а также данные из глобального объекта `post`, таким образом мы получим доступ ко всем полям из таблицы `wp_posts`.