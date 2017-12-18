<?php
/**
 * Miso\Help
 *
 * @author     Jidaikobo Inc.
 * @license    The MIT License (MIT)
 * @copyright  Jidaikobo Inc.
 * @link       http://www.jidaikobo.com
 */
namespace Miso;

class Help
{
public static function ussage ()
{
?>
<div class="wrap">
<div id="icon-themes" class="icon32"><br /></div>
<h1><?php echo __('Help')  ?></h1>
<div class="inside">

<h2>\Miso\Miso_Controller</h2>
<pre>
static::url($action) // eg: 'edit' => admin_url(page=MisoController_Foo&action=edit)
static::redirect($action)
</pre>

<h2>\Miso\Miso</h2>
<pre>
Miso::redirect($controller, $action)
Miso::get($type) // ['controllers', 'models', 'views']
Miso::path2classname($type, $path[, $parent])
Miso::classname2slug($classname)
Miso::slug2classname($slug)
Miso::slug2classname($slug)
</pre>

<h2>\Miso\Arr</h2>
<pre>
Arr::set($arr, $key, $value)
Arr::get($arr, $key[, $default])
</pre>

<h2>\Miso\Session</h2>
<pre>
Session::set($realm, $key, $vals)
Session::add($realm, $key, $vals)
Session::remove($realm[, $key, $vals])
Session::fetch($realm[, $key]) // delete valus
Session::show($realm[, $key])
</pre>

<h2>\Miso\Util</h2>
<pre>
Util::uri() // current uri
Util::rootRelative() // root relative current uri
Util::addQueryStrings($uri, $query_strings = array()) // $query_strings array(array('key', 'val'),...)
Util::removeQueryStrings($uri, $query_strings = array()) // $query_strings array('key',....)
Util::truncate($str, $len, $lead = '...')
Util::error($message = '')
</pre>

<h2>\Miso\View</h2>
<pre>
View::assign($key, $val, $escape = TRUE);
$v = View::fetch($key);
$v = View::fetch_tpl('dir/edit.php');
</pre>

<h2>\Miso\Field</h2>
<pre>
Field::input();
</pre>

</div><!--/.inside-->
</div><!--/.wrap-->
<?php
}
}
