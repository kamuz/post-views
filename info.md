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

*wp-content/plugins/kmz-post-views/kmz-post-views.php*

```php
add_filter('the_content', 'kmz_post_views');

function kmz_post_views($content){
    if(is_page()) return $content;
    global $post;
    return $content . "<p><b>Views:</b> " . $views . "</p>";
}
```

Обновим значение количества просмотров если мы переходим внутрь статьи:

*wp-content/plugins/kmz-post-views/kmz-post-views.php*

```php
function kmz_post_views($content){
    if(is_page()) return $content;
    global $post;
    $views = $post->post_views;
    if(is_single()) $views += 1;
    return $content . "<p><b>Views:</b> " . $views . "</p>";
}
```

На хук `wp_head` мы вешаем действие на добавление количества просмотров и сохранение в БД:

*wp-content/plugins/kmz-post-views/kmz-post-views.php*

```php
add_filter('the_content', 'kmz_post_views');

function kmz_post_views($content){
    if(is_page()) return $content;
    global $post;
    $views = $post->post_views;
    return $content . "<p><b>Views:</b> " . $views . "</p>";
}

add_action('wp_head', 'kmz_add_view');

function kmz_add_view(){
    if(!is_single()) return;
    global $post, $wpdb;
    $id = $post->ID;
    $views = $post->post_views += 1;
    $wpdb->update(
        $wpdb->posts,
        array('post_views' => $views),
        array('ID' => $id)
    );
}
```

Если мы деактивируем наш плагин, а потом попробуем его снова активировать, но у нас в логах появится некритическая ошибка SQL запроса, потому что мы пытаемся ещё раз создать уже имеющееся поле в БД `post_views`. Похожая ситуация возникает тогда когда мы установим плагин, а потом не активируя его, сразу его удалим - также будет некритическая ошибка, возникающая при попытке удаления не существующего поля `post_views`, потому что это поле у нас создаётся только после активации плагина.

Один из вариантов решения этой проблемы будет создать SQL запрос, которые выберет все поля с таблицы `wp_posts` и затем мы сделаем проверку, если существует поле `post_views` и затем уже мы будем выполнять или не выполнять необходимый нам код.

Для начала получим данные о всех полях в виде массива с помощью метода `get_results()`:

*wp-content/plugins/kmz-post-views/kmz-post-views.php*

```php
function kmz_add_view(){
    if(!is_single()) return;
    global $post, $wpdb;

    $fields = $wpdb->get_results("SHOW fields FROM $wpdb->posts", ARRAY_A);
    echo "<pre>";
    print_r($fields);
    echo "</pre>";

    $id = $post->ID;
    $views = $post->post_views += 1;
    $wpdb->update(
        $wpdb->posts,
        array('post_views' => $views),
        array('ID' => $id)
    );
}
```

Теперь зная что нам нужно получить, мы можем пройтись в цикле и сделать проверку:

*wp-content/plugins/kmz-post-views/kmz-post-views.php*

```php

function kmz_add_view(){
    if(!is_single()) return;
    global $post, $wpdb;

    $fields = $wpdb->get_results("SHOW fields FROM $wpdb->posts", ARRAY_A);
    foreach($fields as $field){
        if($field['Field'] == 'post_views'){
            echo "Okay";
            break;
        }
    }

    $id = $post->ID;
    $views = $post->post_views += 1;
    $wpdb->update(
        $wpdb->posts,
        array('post_views' => $views),
        array('ID' => $id)
    );
}
```

Теперь можно этот код вынести в отдельную функцию и отдельный файл:

*wp-content/plugins/kmz-post-views/kmz-functions.php*

```php
function kmz_has_field($table, $column){
    global $wpdb;

    $fields = $wpdb->get_results("SHOW fields FROM $table", ARRAY_A);

    foreach($fields as $field){
        if($field['Field'] == $column){
            return true;
        }
    }
    return false;
}
```

Теперь в нам нужно подключить этой файл и сделать проверку в необходимых файлах:

*wp-content/plugins/kmz-post-views/kmz-post-views.php*

```php
include(dirname(__FILE__) . '/kmz-functions.php');

/**
 * Add new field in the DB table wp_posts
 */

register_activation_hook(__FILE__, 'kmz_post_create_field');

function kmz_post_create_field(){
    global $wpdb;
    if( !kmz_has_field($wpdb->posts, 'post_views') ){
        $query = "ALTER TABLE $wpdb->posts ADD post_views INT NOT NULL DEFAULT '0'";
        $wpdb->query($query);
    }
}
//..
```

*wp-content/plugins/kmz-post-views/uninstall.php*

```php
include(dirname(__FILE__) . '/kmz-functions.php');

if( !defined('WP_UNINSTALL_PLUGIN') ) exit;

global $wpdb;

if( kmz_has_field($wpdb->posts, 'post_views') ){
    $query = "ALTER TABLE $wpdb->posts DROP post_views";
    $wpdb->query($query);
}
```