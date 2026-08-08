<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Magic constants - Manual</title>
 <style type="text/css" media="all">
  @import url("@w{2XX58MCD}");
  @import url("@w{884KPP5P}");
  
 </style>
 <!--[if IE]><![if gte IE 6]><![endif]-->
  <style type="text/css" media="print">
   @import url("@w{M98RFPWS}");
  </style>
 <!--[if IE]><![endif]><![endif]-->
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
 <link rel="shortcut icon" href="@w{NGWYKJ8F}" />
 <link rel="contents" href="index.php" />
 <link rel="index" href="language.constants.php" />
 <link rel="prev" href="language.constants.syntax.php" />
 <link rel="next" href="language.expressions.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/constants.predefined" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.constants.predefined.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{VWAJKDBA}" />
 <meta http-equiv="Content-language" content="en" />
            <script type="text/javascript" src="@w{ME5H2G8Y}"></script>
            <script type="text/javascript" src="@w{BYSKBGP9}"></script>
<script type="text/javascript">
$(document).ready(function() {
    var toggleImage = function(elem) {
        if ($(elem).hasClass("shown")) {
            $(elem).removeClass("shown").addClass("hidden");
            $("img", elem).attr("src", "/images/notes-add.gif");
        }
        else {
            $(elem).removeClass("hidden").addClass("shown");
            $("img", elem).attr("src", "/images/notes-reject.gif");
        }
    };

    $(".soft-deprecation-notice h1.title").each(function() {
        $(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' alt='minimize' /></a> ");
    });
    $(".refsect1 h3.title").each(function() {
        url = "@w{BD87E369}" + $(this).parent().parent().attr("id") + "%23" + $(this).parent().attr("id");
        $(this).parent().prepend("<div class='reportbug'><a href='" + url + "'>Report a bug</a></div>");
        $(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' alt='reject note' /></a> ");
    });
    $("#usernotes .head").each(function() {
        $(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' alt='reject note' /></a> ");
    });
    $(".soft-deprecation-notice h1.title .toggler").click(function() {
        $(this).parent().siblings().slideToggle("slow");
        toggleImage(this);
        return false;
    });
    $(".refsect1 h3.title .toggler").click(function() {
        $(this).parent().siblings().slideToggle("slow");
        toggleImage(this);
        return false;
    });
    $("#usernotes .head .toggler").click(function() {
        $(this).parent().next().slideToggle("slow");
        toggleImage(this);
        return false;
    });
});
</script>

</head>
<body>

<div id="headnav">
 <a href="/" rel="home"><img src="@w{BJ2SG82M}"
 alt="PHP" width="120" height="67" id="phplogo" /></a>
 <div id="headmenu">
  <a href="/downloads.php">downloads</a> |
  <a href="/docs.php">documentation</a> |
  <a href="/FAQ.php">faq</a> |
  <a href="/support.php">getting help</a> |
  <a href="/mailing-lists.php">mailing lists</a> |
  <a href="/license">licenses</a> |
  <a href="@w{WEGCK3BV}">wiki</a> |
  <a href="@w{JBVFFY7T}">reporting bugs</a> |
  <a href="/sites.php">php.net sites</a> |
  <a href="/conferences/">conferences</a> |
  <a href="/my.php">my php.net</a>
 </div>
</div>

<div id="headsearch">
 <form method="post" action="/search.php" id="topsearch">
  <p>
   <span title="Keyboard shortcut: Alt+S (Win), Ctrl+S (Apple)">
    <span class="shortkey">s</span>earch for
   </span>
   <input type="text" name="pattern" value="" size="30" accesskey="s" />
   <span>in the</span>
   <select name="show">
    <option value="all"      >all php.net sites</option>
    <option value="local"    >this mirror only</option>
    <option value="quickref" selected="selected">function list</option>
    <option value="manual"   >online documentation</option>
    <option value="bugdb"    >bug database</option>
    <option value="news_archive">Site News Archive</option>
    <option value="changelogs">All Changelogs</option>
    <option value="pear"     >just pear.php.net</option>
    <option value="pecl"     >just pecl.php.net</option>
    <option value="talks"    >just talks.php.net</option>
    <option value="maillist" >general mailing list</option>
    <option value="devlist"  >developer mailing list</option>
    <option value="phpdoc"   >documentation mailing list</option>
   </select>
   <input type="image"
          src="@w{XXWWP636}"
          class="submit" alt="search" />
   <input type="hidden" name="lang" value="en" />
  </p>
 </form>
</div>

<div id="layout_2">
 <div id="leftbar">
<!--UdmComment-->
<ul class="toc">
 <li class="header home"><a href="index.php">PHP Manual</a></li>
 <li class="header up"><a href="langref.php">Language Reference</a></li>
 <li class="header up"><a href="language.constants.php">Constants</a></li>
 <li><a href="language.constants.syntax.php">Syntax</a></li>
 <li class="active"><a href="language.constants.predefined.php">Magic constants</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.expressions.php">Expressions<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.constants.syntax.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Syntax</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.constants.predefined.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.constants.predefined.php">Brazilian Portuguese</option>
    <option value="zh/language.constants.predefined.php">Chinese (Simplified)</option>
    <option value="fr/language.constants.predefined.php">French</option>
    <option value="de/language.constants.predefined.php">German</option>
    <option value="ja/language.constants.predefined.php">Japanese</option>
    <option value="pl/language.constants.predefined.php">Polish</option>
    <option value="ro/language.constants.predefined.php">Romanian</option>
    <option value="ru/language.constants.predefined.php">Russian</option>
    <option value="fa/language.constants.predefined.php">Persian</option>
    <option value="es/language.constants.predefined.php">Spanish</option>
    <option value="tr/language.constants.predefined.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.constants.predefined" class="sect1">
   <h2 class="title">Magic constants</h2>

   <p class="simpara">
    PHP provides a large number of <a href="reserved.constants.php" class="link">predefined constants</a> to any script
    which it runs. Many of these constants, however, are created by
    various extensions, and will only be present when those extensions
    are available, either via dynamic loading or because they have
    been compiled in.
   </p>
   
   <p class="para">
    There are eight magical constants that change depending on
    where they are used.  For example, the value of
    <strong><code>__LINE__</code></strong> depends on the line that it&#039;s
    used on in your script. These special constants are 
    case-insensitive and are as follows:
   </p>
   <p class="para">
    <table class="doctable table">
     <caption><strong>A few &quot;magical&quot; PHP constants</strong></caption>
     
      <thead>
       <tr>
        <th>Name</th>
        <th>Description</th>
       </tr>

      </thead>

      <tbody class="tbody">
       <tr id="constant.line">
        <td><strong><code>__LINE__</code></strong></td>
        <td>
         The current line number of the file.
        </td>
       </tr>

       <tr id="constant.file">
        <td><strong><code>__FILE__</code></strong></td>
        <td>
         The full path and filename of the file.  If used inside an include,
         the name of the included file is returned.
         Since PHP 4.0.2, <strong><code>__FILE__</code></strong> always contains an
         absolute path with symlinks resolved whereas in older versions it contained relative path
         under some circumstances.
        </td>
       </tr>

       <tr id="constant.dir">
        <td><strong><code>__DIR__</code></strong></td>
        <td>
         The directory of the file.  If used inside an include,
         the directory of the included file is returned. This is equivalent
         to <em>dirname(__FILE__)</em>. This directory name
         does not have a trailing slash unless it is the root directory.
         (Added in PHP 5.3.0.)
        </td>
       </tr>

       <tr id="constant.function">
        <td><strong><code>__FUNCTION__</code></strong></td>
        <td>
         The function name. (Added in PHP 4.3.0)  As of PHP 5 this constant 
         returns the function name as it was declared (case-sensitive).  In
         PHP 4 its value is always lowercased.
        </td>
       </tr>

       <tr id="constant.class">
        <td><strong><code>__CLASS__</code></strong></td>
        <td>
         The class name. (Added in PHP 4.3.0)  As of PHP 5 this constant 
         returns the class name as it was declared (case-sensitive).  In PHP
         4 its value is always lowercased.  The class name includes the namespace
         it was declared in (e.g. <em>Foo\Bar</em>).
         Note that as of PHP 5.4 __CLASS__ works also in traits. When used
         in a trait method, __CLASS__ is the name of the class the trait
         is used in.
        </td>
       </tr>

       <tr id="constant.trait">
        <td><strong><code>__TRAIT__</code></strong></td>
        <td>
         The trait name. (Added in PHP 5.4.0)  As of PHP 5.4 this constant 
         returns the trait as it was declared (case-sensitive).  The trait name includes the namespace
         it was declared in (e.g. <em>Foo\Bar</em>).
        </td>
       </tr>

       
       <tr id="constant.method">
        <td><strong><code>__METHOD__</code></strong></td>
        <td>
         The class method name. (Added in PHP 5.0.0)  The method name is
         returned as it was declared (case-sensitive).
        </td>
       </tr>

       <tr id="constant.namespace">
        <td><strong><code>__NAMESPACE__</code></strong></td>
        <td>
         The name of the current namespace (case-sensitive). This constant 
         is defined in compile-time (Added in PHP 5.3.0).
        </td>
       </tr>

      </tbody>
     
    </table>

   </p>
   <p class="para">
    See also 
     <span class="function"><a href="function.get-class.php" class="function">get_class()</a></span>,
     <span class="function"><a href="function.get-object-vars.php" class="function">get_object_vars()</a></span>,
     <span class="function"><a href="function.file-exists.php" class="function">file_exists()</a></span> and
     <span class="function"><a href="function.function-exists.php" class="function">function_exists()</a></span>.
   </p>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.expressions.php">Expressions<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.constants.syntax.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Syntax</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.constants.predefined.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.constants.predefined&amp;redirect=@w{VWAJKDBA}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.constants.predefined&amp;redirect=@w{VWAJKDBA}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Magic constants</strong>
 </div><div id="allnotes">
 <a name="108797"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#108797" class="date">25-May-2012 08:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A note about __FUNCTION__ and create_function():<br />
<br />
If you use __FUNCTION__ inside the body of a function you create with create_function(), the __FUNCTION__ always evaluates to the string "__lambda_func" (even in different functions created by create_function()), not the function name that is returned by create_function().</span>
</code></div>
  </div>
 </div>
 <a name="108653"></a>
 <div class="note">
  <strong class='user'>tc0nn</strong>
  <a href="#108653" class="date">15-May-2012 05:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Also worth noting, I use a extreme-logger when doing intense troubleshooting. It basically does a debug_backtrace and logs certain info. I noticed on some older PHP installs (&lt;5) I had to prepend __FILE__ and __LINE__ with ''. just to force PHP output a string. Specifically I was loading those two in an array which were concat'd onto a log file eventually.<br />
<br />
example:<br />
if($a['debug']){$of-&gt;logger(array('file'=&gt;__FILE__,'line'=&gt;''.__LINE__,'data'=&gt;$sql_data_array));}</span>
</code></div>
  </div>
 </div>
 <a name="107614"></a>
 <div class="note">
  <strong class='user'>david at thegallagher dot net</strong>
  <a href="#107614" class="date">22-Feb-2012 01:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You cannot check if a magic constant is defined. This means there is no point in checking if __DIR__ is defined then defining it. `defined('__DIR__')` always returns false. Defining __DIR__ will silently fail in PHP 5.3+. This could cause compatibility issues if your script includes other scripts.<br />
<br />
Here is proof:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">echo (</span><span class="default">defined</span><span class="keyword">(</span><span class="string">'__DIR__'</span><span class="keyword">) ? </span><span class="string">'__DIR__ is defined' </span><span class="keyword">: </span><span class="string">'__DIR__ is NOT defined' </span><span class="keyword">. </span><span class="default">PHP_EOL</span><span class="keyword">);<br />
echo (</span><span class="default">defined</span><span class="keyword">(</span><span class="string">'__FILE__'</span><span class="keyword">) ? </span><span class="string">'__FILE__ is defined' </span><span class="keyword">: </span><span class="string">'__FILE__ is NOT defined' </span><span class="keyword">. </span><span class="default">PHP_EOL</span><span class="keyword">);<br />
echo (</span><span class="default">defined</span><span class="keyword">(</span><span class="string">'PHP_VERSION'</span><span class="keyword">) ? </span><span class="string">'PHP_VERSION is defined' </span><span class="keyword">: </span><span class="string">'PHP_VERSION is NOT defined'</span><span class="keyword">) . </span><span class="default">PHP_EOL</span><span class="keyword">;<br />
echo </span><span class="string">'PHP Version: ' </span><span class="keyword">. </span><span class="default">PHP_VERSION </span><span class="keyword">. </span><span class="default">PHP_EOL</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Output:<br />
__DIR__ is NOT defined<br />
__FILE__ is NOT defined<br />
PHP_VERSION is defined<br />
PHP Version: 5.3.6</span>
</code></div>
  </div>
 </div>
 <a name="107002"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#107002" class="date">27-Dec-2011 08:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Further clarification on the __TRAIT__ magic constant.<br />
<br />
<span class="default">&lt;?php<br />
trait PeanutButter </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">traitName</span><span class="keyword">() {echo </span><span class="default">__TRAIT__</span><span class="keyword">;}<br />
}<br />
<br />
</span><span class="default">trait PeanutButterAndJelly </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; use </span><span class="default">PeanutButter</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">Test </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; use </span><span class="default">PeanutButterAndJelly</span><span class="keyword">;<br />
}<br />
<br />
(new </span><span class="default">Test</span><span class="keyword">)-&gt;</span><span class="default">traitName</span><span class="keyword">(); </span><span class="comment">//PeanutButter<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="105256"></a>
 <div class="note">
  <strong class='user'>jrivero24 at yahoo dot es</strong>
  <a href="#105256" class="date">05-Aug-2011 02:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When __DIR__ is not defined, prior 5.3.0:<br />
<br />
<span class="default">&lt;?php </span><span class="keyword">if ( !</span><span class="default">defined</span><span class="keyword">(</span><span class="string">'__DIR__'</span><span class="keyword">) ) </span><span class="default">define</span><span class="keyword">(</span><span class="string">'__DIR__'</span><span class="keyword">, </span><span class="default">dirname</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">)); </span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="104842"></a>
 <div class="note">
  <strong class='user'>user9 at voloreport dot com</strong>
  <a href="#104842" class="date">10-Jul-2011 12:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that __FILE__ has a quirk when used inside an eval() call. It will tack on something like "(80) : eval()'d code" (the number may change) on the end of the string at run-time. The workaround is:<br />
<br />
$script = php_strip_whitespace('myprogram.php');<br />
$script = str_replace('__FILE__',"preg_replace('@\(.*\(.*$@', '', __FILE__,1)",$script);<br />
eval($script);</span>
</code></div>
  </div>
 </div>
 <a name="103556"></a>
 <div class="note">
  <strong class='user'>chris dot kistner at gmail dot com</strong>
  <a href="#103556" class="date">20-Apr-2011 05:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
There is no way to implement a backwards compatible __DIR__ in versions prior to 5.3.0.<br />
<br />
The only thing that you can do is to perform a recursive search and replace to dirname(__FILE__):<br />
find . -type f -print0 | xargs -0 sed -i 's/__DIR__/dirname(__FILE__)/'</span>
</code></div>
  </div>
 </div>
 <a name="102733"></a>
 <div class="note">
  <strong class='user'>Jamie</strong>
  <a href="#102733" class="date">02-Mar-2011 01:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that as mentioned, __FILE__ resolves any aliases. Other real path information, such as $_SERVER["SCRIPT_FILENAME"], doesn't.<br />
<br />
__FILE__ =&gt; /volume1/web/mysite/admin/inc/includeFile.inc.php<br />
$_SERVER["SCRIPT_FILENAME"] =&gt; /var/services/web/mysite/admin/products.php<br />
<br />
If you need to compare one with the other, use <br />
realpath($_SERVER["SCRIPT_FILENAME"])</span>
</code></div>
  </div>
 </div>
 <a name="100806"></a>
 <div class="note">
  <strong class='user'>stefan at efectos dot nl</strong>
  <a href="#100806" class="date">08-Nov-2010 05:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When __DIR__ is not defined, you can also use this workaround to generate it:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if(!</span><span class="default">defined</span><span class="keyword">(</span><span class="string">'__DIR__'</span><span class="keyword">)) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$iPos </span><span class="keyword">= </span><span class="default">strrpos</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">, </span><span class="string">"/"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">define</span><span class="keyword">(</span><span class="string">"__DIR__"</span><span class="keyword">, </span><span class="default">substr</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">$iPos</span><span class="keyword">) . </span><span class="string">"/"</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Keep in mind this sets __DIR__ to the directory you are running this snippet from.</span>
</code></div>
  </div>
 </div>
 <a name="99849"></a>
 <div class="note">
  <strong class='user'>madboyka at yahoo dot com</strong>
  <a href="#99849" class="date">10-Sep-2010 07:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Since namespace were introduced, it would be nice to have a magic constant or function (like get_class()) which would return the class name without the namespaces.<br />
<br />
On windows I used basename(__CLASS__). (LOL)</span>
</code></div>
  </div>
 </div>
 <a name="99278"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#99278" class="date">08-Aug-2010 03:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
__DIR__ befor PHP 5.3.0<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (!</span><span class="default">defined</span><span class="keyword">(</span><span class="string">'__DIR__'</span><span class="keyword">)) {<br />
&nbsp; class </span><span class="default">__FILE_CLASS__ </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function&nbsp; </span><span class="default">__toString</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$X </span><span class="keyword">= </span><span class="default">debug_backtrace</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp;&nbsp; return </span><span class="default">dirname</span><span class="keyword">(</span><span class="default">$X</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">][</span><span class="string">'file'</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
&nbsp; </span><span class="default">define</span><span class="keyword">(</span><span class="string">'__DIR__'</span><span class="keyword">, new </span><span class="default">__FILE_CLASS__</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="84050"></a>
 <div class="note">
  <strong class='user'>me at jamessocol dot com</strong>
  <a href="#84050" class="date">25-Jun-2008 09:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
We need an eighth magic constant, something along the lines of __STATIC__. This should return the name of the class from which a static method was called, regardless of where in the inheritance tree the method was defined.<br />
<br />
PHP 5.3 has the new use of the static keyword which will help, but it isn't perfect. You still have to repeat yourself frequently.<br />
<br />
For example, trying to implement Active Record:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// In PHP 5.3<br />
<br />
</span><span class="keyword">class </span><span class="default">Model<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public static function </span><span class="default">find</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo static::</span><span class="default">$class</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">Product </span><span class="keyword">extends </span><span class="default">Model<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected static </span><span class="default">$class </span><span class="keyword">= </span><span class="default">__CLASS__</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">User </span><span class="keyword">extends </span><span class="default">Model<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected static </span><span class="default">$class </span><span class="keyword">= </span><span class="default">__CLASS__</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">Product</span><span class="keyword">::</span><span class="default">find</span><span class="keyword">(); </span><span class="comment">// "Product"<br />
</span><span class="default">User</span><span class="keyword">::</span><span class="default">find</span><span class="keyword">(); </span><span class="comment">// "User"<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// With __STATIC__ keyword. (Would be better.)<br />
<br />
</span><span class="keyword">class </span><span class="default">Model<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public static function </span><span class="default">find</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">__STATIC__</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">Product </span><span class="keyword">extends </span><span class="default">Model </span><span class="keyword">{}<br />
<br />
class </span><span class="default">User </span><span class="keyword">extends </span><span class="default">Model </span><span class="keyword">{}<br />
<br />
</span><span class="default">Product</span><span class="keyword">::</span><span class="default">find</span><span class="keyword">(); </span><span class="comment">// "Product"<br />
</span><span class="default">User</span><span class="keyword">::</span><span class="default">find</span><span class="keyword">(); </span><span class="comment">// "User"<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
[EDITED : Use get_called_class()]</span>
</code></div>
  </div>
 </div>
 <a name="75894"></a>
 <div class="note">
  <strong class='user'>php at kennel17 dot co dot uk</strong>
  <a href="#75894" class="date">20-Jun-2007 10:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Further to my previous note, the 'object' element of the array can be used to get the parent object.&nbsp; So changing the get_class_static() function to the following will make the code behave as expected:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">get_class_static</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$bt </span><span class="keyword">= </span><span class="default">debug_backtrace</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (isset(</span><span class="default">$bt</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">][</span><span class="string">'object'</span><span class="keyword">]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$bt</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">][</span><span class="string">'object'</span><span class="keyword">]);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$bt</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">][</span><span class="string">'class'</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;<br />
</span><br />
HOWEVER, it still fails when being called statically.&nbsp; Changing the last two lines of my previous example to<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; foo</span><span class="keyword">::</span><span class="default">printClassName</span><span class="keyword">();<br />
&nbsp; </span><span class="default">bar</span><span class="keyword">::</span><span class="default">printClassName</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
...still gives the same problematic result in PHP5, but in this case the 'object' property is not set, so that technique is unavailable.</span>
</code></div>
  </div>
 </div>
 <a name="75891"></a>
 <div class="note">
  <strong class='user'>php at kennel17 dot co dot uk</strong>
  <a href="#75891" class="date">20-Jun-2007 09:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In response to stangelanda at gmail dot com, (who suggested a possible fix to get the actual class name of the object, when being called statically).<br />
<br />
in PHP5, this fix no longer works.&nbsp; <br />
<br />
Here is some example code:<br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp; </span><span class="keyword">function </span><span class="default">get_class_static</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$bt </span><span class="keyword">= </span><span class="default">debug_backtrace</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$name </span><span class="keyword">= </span><span class="default">$bt</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">][</span><span class="string">'class'</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$name</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; class </span><span class="default">foo </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">printClassName</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp;&nbsp; print(</span><span class="default">get_class_static</span><span class="keyword">() . </span><span class="string">"&lt;br&gt;"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; }<br />
&nbsp; }<br />
<br />
&nbsp; class </span><span class="default">bar </span><span class="keyword">extends </span><span class="default">foo </span><span class="keyword">{<br />
&nbsp; }<br />
<br />
</span><span class="default">$f </span><span class="keyword">= new </span><span class="default">foo</span><span class="keyword">();<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">bar</span><span class="keyword">();<br />
</span><span class="default">$f</span><span class="keyword">-&gt;</span><span class="default">printClassName</span><span class="keyword">();<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">printClassName</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
In PHP4, it outputs<br />
&nbsp; foo<br />
&nbsp; bar<br />
as you described.<br />
<br />
However, in PHP5, due to the way the debug_backtrace() function has been modified (see <a href="http://bugs.php.net/bug.php?id=30828" rel="nofollow" target="_blank">http://bugs.php.net/bug.php?id=30828</a>) the output is now<br />
&nbsp; foo<br />
&nbsp; foo<br />
<br />
I have yet to figure out a way to get the original output in PHP5.&nbsp; Any suggestions would be very useful, and if I find an answer I'll post it here.</span>
</code></div>
  </div>
 </div>
 <a name="71064"></a>
 <div class="note">
  <strong class='user'>Tomek Perlak [tomekperlak at tlen pl]</strong>
  <a href="#71064" class="date">10-Nov-2006 02:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The __CLASS__ magic constant nicely complements the get_class() function.<br />
<br />
Sometimes you need to know both:<br />
- name of the inherited class<br />
- name of the class actually executed<br />
<br />
Here's an example that shows the possible solution:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">base_class<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">say_a</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"'a' - said the " </span><span class="keyword">. </span><span class="default">__CLASS__ </span><span class="keyword">. </span><span class="string">"&lt;br/&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">say_b</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"'b' - said the " </span><span class="keyword">. </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">) . </span><span class="string">"&lt;br/&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
}<br />
<br />
class </span><span class="default">derived_class </span><span class="keyword">extends </span><span class="default">base_class<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">say_a</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">parent</span><span class="keyword">::</span><span class="default">say_a</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"'a' - said the " </span><span class="keyword">. </span><span class="default">__CLASS__ </span><span class="keyword">. </span><span class="string">"&lt;br/&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">say_b</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">parent</span><span class="keyword">::</span><span class="default">say_b</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"'b' - said the " </span><span class="keyword">. </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">) . </span><span class="string">"&lt;br/&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$obj_b </span><span class="keyword">= new </span><span class="default">derived_class</span><span class="keyword">();<br />
<br />
</span><span class="default">$obj_b</span><span class="keyword">-&gt;</span><span class="default">say_a</span><span class="keyword">();<br />
echo </span><span class="string">"&lt;br/&gt;"</span><span class="keyword">;<br />
</span><span class="default">$obj_b</span><span class="keyword">-&gt;</span><span class="default">say_b</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
The output should look roughly like this:<br />
<br />
'a' - said the base_class<br />
'a' - said the derived_class<br />
<br />
'b' - said the derived_class<br />
'b' - said the derived_class</span>
</code></div>
  </div>
 </div>
 <a name="69433"></a>
 <div class="note">
  <strong class='user'>stangelanda at gmail dot com</strong>
  <a href="#69433" class="date">05-Sep-2006 09:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
claude noted that __CLASS__ always contains the class that it is called in, if you would rather have the class that called the method use get_class($this) instead.&nbsp; However this only works with instances, not when called statically. <br />
<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; function </span><span class="default">showclass</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; echo </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; }<br />
&nbsp; }<br />
<br />
&nbsp; class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A </span><span class="keyword">{}<br />
<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">();<br />
&nbsp; </span><span class="default">$b </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">();<br />
<br />
&nbsp; </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">showclass</span><span class="keyword">();<br />
&nbsp; </span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">showclass</span><span class="keyword">();<br />
&nbsp; </span><span class="default">A</span><span class="keyword">::</span><span class="default">showclass</span><span class="keyword">();<br />
&nbsp; </span><span class="default">B</span><span class="keyword">::</span><span class="default">showclass</span><span class="keyword">();<br />
<br />
&nbsp; </span><span class="comment">//results in "a", "b", false, false<br />
</span><span class="default">?&gt;<br />
</span><br />
I tried keeping track of the class manually within the properties, but the following doesn't work either:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; var </span><span class="default">$class </span><span class="keyword">= </span><span class="default">__CLASS__</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; function </span><span class="default">showclass</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">class</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; }<br />
&nbsp; }<br />
<br />
&nbsp; class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; var </span><span class="default">$class </span><span class="keyword">= </span><span class="default">__CLASS__</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; </span><span class="comment">//results in "a", "b", NULL, NULL<br />
</span><span class="default">?&gt;<br />
</span><br />
The best solution I could come up with was using debug_backtrace.&nbsp; I assume there is a better way somehow, but I can't find it.&nbsp; However the following works:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; function </span><span class="default">showclass</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$backtrace </span><span class="keyword">= </span><span class="default">debug_backtrace</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$backtrace</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">][</span><span class="string">'class'</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; }<br />
&nbsp; }<br />
<br />
&nbsp; class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A </span><span class="keyword">{}<br />
<br />
&nbsp; </span><span class="comment">//results in "a", "b", "a", "b"<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="59871"></a>
 <div class="note">
  <strong class='user'>warhog at warhog dot net</strong>
  <a href="#59871" class="date">18-Dec-2005 01:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
There is another magic constant not mentioned above: __COMPILER_HALT_OFFSET__ - contains where the compiler halted - see <a href="http://www.php.net/manual/function.halt-compiler.php" rel="nofollow" target="_blank">http://www.php.net/manual/function.halt-compiler.php</a> for further information.</span>
</code></div>
  </div>
 </div>
 <a name="57033"></a>
 <div class="note">
  <strong class='user'>vijaykoul_007 at rediffmail dot com</strong>
  <a href="#57033" class="date">21-Sep-2005 09:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
the difference between <br />
__FUNCTION__ and __METHOD__ as in PHP 5.0.4 is that<br />
<br />
__FUNCTION__ returns only the name of the function<br />
<br />
while as __METHOD__ returns the name of the class alongwith the name of the function<br />
<br />
class trick<br />
{<br />
&nbsp;&nbsp; &nbsp;&nbsp; function doit()<br />
&nbsp;&nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo __FUNCTION__;<br />
&nbsp;&nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp;&nbsp; function doitagain()<br />
&nbsp;&nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo __METHOD__;<br />
&nbsp;&nbsp; &nbsp;&nbsp; }<br />
}<br />
$obj=new trick();<br />
$obj-&gt;doit();<br />
output will be ----&nbsp; doit<br />
$obj-&gt;doitagain();<br />
output will be ----- trick::doitagain</span>
</code></div>
  </div>
 </div>
 <a name="50579"></a>
 <div class="note">
  <strong class='user'>karl __at__ streetlampsoftware__dot__com</strong>
  <a href="#50579" class="date">03-Mar-2005 01:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that the magic constants cannot be included in quoted strings. <br />
<br />
For instance, <br />
echo "This is the filename: __FILE__";<br />
will return exactly what's typed above.<br />
<br />
echo "This is the filename: {__FILE__}";<br />
will also return what's typed above.<br />
<br />
The only way to get magic constants to parse in strings is to concatenate them into strings:<br />
echo "This is the filename: ".__FILE__;</span>
</code></div>
  </div>
 </div>
 <a name="50561"></a>
 <div class="note">
  <strong class='user'>csaba at alum dot mit dot edu</strong>
  <a href="#50561" class="date">03-Mar-2005 04:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Sometimes you might want to know whether a script is the top level script or whether it has been included.&nbsp; That could be useful if you want to reuse the routines in another script, but you don't want to separate them out.&nbsp; Here's a way that seems to be working for me (for both Apache2 module and CLI versions of PHP) on my Win XP Pro system.<br />
<br />
By the way, if __FILE__ is within a function call, its value corresponds to the file it was defined in and not the file that it was called from.&nbsp; Also, I used $script and strtolower instead of realpath because if the script is deleted after inclusion but before realpath is called (which could happen if the test is deferred), then realpath would return empty since it requires an extant file or directory.<br />
<br />
Csaba Gabor from Vienna<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (</span><span class="default">amIincluded</span><span class="keyword">()) return;&nbsp; &nbsp; </span><span class="comment">// if we're included we only want function defs<br />
</span><span class="keyword">function </span><span class="default">amIincluded</span><span class="keyword">() {<br />
</span><span class="comment">//&nbsp; &nbsp; returns true/false depending on whether the currently<br />
//&nbsp; &nbsp; executing script is included or not<br />
//&nbsp; &nbsp; Don't put this function in an include file (duh)!<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$webP </span><span class="keyword">= !!</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_METHOD'</span><span class="keyword">];&nbsp; &nbsp; </span><span class="comment">// a web request?<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$script </span><span class="keyword">= </span><span class="default">preg_replace</span><span class="keyword">(</span><span class="string">'/\//'</span><span class="keyword">,</span><span class="default">DIRECTORY_SEPARATOR</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'SCRIPT_FILENAME'</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; return (</span><span class="default">$webP</span><span class="keyword">) ? (</span><span class="default">strtolower</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">)!=</span><span class="default">strtolower</span><span class="keyword">(</span><span class="default">$script</span><span class="keyword">)) :<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; !</span><span class="default">array_key_exists</span><span class="keyword">(</span><span class="string">"_REQUEST"</span><span class="keyword">, </span><span class="default">$GLOBALS</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="48008"></a>
 <div class="note">
  <strong class='user'>lm arobase bible point ch</strong>
  <a href="#48008" class="date">08-Dec-2004 02:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
in reply to x123 at bestof dash inter:<br />
I believe, this is not a bug, but a feature.<br />
__FILE__ returns the name of the include file, while $PHP_SELF returns the relative name of the main file.<br />
It is then easy to get the file name only with substr(strrchr($PHP_SELF,'/'),1)</span>
</code></div>
  </div>
 </div>
 <a name="44214"></a>
 <div class="note">
  <strong class='user'>claude at NOSPAM dot claude dot nl</strong>
  <a href="#44214" class="date">18-Jul-2004 08:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that __CLASS__ contains the class it is called in; in lowercase. So the code:<br />
<br />
class A<br />
{<br />
&nbsp;&nbsp;&nbsp; function showclass()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo __CLASS__;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class B extends A<br />
{<br />
}<br />
<br />
$a = new A();<br />
$b = new B();<br />
<br />
$a-&gt;showclass();<br />
$b-&gt;showclass();<br />
A::showclass();<br />
B::showclass();<br />
<br />
results in "aaaa";</span>
</code></div>
  </div>
 </div>
 <a name="40458"></a>
 <div class="note">
  <strong class='user'>ulrik</strong>
  <a href="#40458" class="date">04-Mar-2004 07:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
note that __FUNCTION__ define gives the the function name in lowercase</span>
</code></div>
  </div>
 </div>
 <a name="39666"></a>
 <div class="note">
  <strong class='user'>warhog at warhog dot net</strong>
  <a href="#39666" class="date">06-Feb-2004 12:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
just to read out the filename of the currently proceeded file use<br />
<span class="default">&lt;?php basename</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">); </span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="32084"></a>
 <div class="note">
  <strong class='user'>hixon at colorado dot edu</strong>
  <a href="#32084" class="date">15-May-2003 05:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can use the following in files that you want to include, but not run directly.&nbsp; The script will exit if it's run as the top-level script, but will not exit if it's included from another script.&nbsp; Of course this won't work in the command line mode. <br />
<br />
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {<br />
&nbsp; exit;<br />
}</span>
</code></div>
  </div>
 </div>
 <a name="29446"></a>
 <div class="note">
  <strong class='user'>kop at meme dot com</strong>
  <a href="#29446" class="date">13-Feb-2003 03:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The keywords TRUE and FALSE (case insensitive), which represent their respective boolean values, are worth noting here.</span>
</code></div>
  </div>
 </div>
 <a name="19913"></a>
 <div class="note">
  <strong class='user'>darwin[at]buchner[dot]net</strong>
  <a href="#19913" class="date">14-Mar-2002 04:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As of version 4.0.6, there is also a handy predefined DIRECTORY_SEPARATOR constant which you can use to make you scripts more portatable between OS's with different directory structures.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.constants.predefined&amp;redirect=@w{VWAJKDBA}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.constants.predefined&amp;redirect=@w{VWAJKDBA}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.constants.predefined.php">show source</a> |
 <a href="/credits.php">credits</a> |
 <a href="/stats/">stats</a> |
 <a href="/sitemap.php">sitemap</a> |
 <a href="/contact.php">contact</a> |
 <a href="/contact.php#ads">advertising</a> |
 <a href="/mirrors.php">mirror sites</a>
</div>

<div id="pagefooter">
 <div id="copyright">
  <a href="/copyright.php">Copyright &copy; 2001-2012 The PHP Group</a><br />
  All rights reserved.
 </div>

 <div id="thismirror">
  <a href="/mirror.php">This mirror</a> generously provided by:
  <a href="@w{TDAY9QJ9}">Yahoo! Inc.</a><br />
  Last updated: Tue Jul 31 20:41:05 2012 UTC
 </div>
</div>
<!--[if IE 6]>
<script type="text/javascript">
    /*Load jQuery if not already loaded*/ if(typeof jQuery == 'undefined'){ document.write("<script type=\"text/javascript\"   src=\"@w{8JFFCNVW}"></"+"script>"); var __noconflict = true; }
    var IE6UPDATE_OPTIONS = {
        icons_path: "/ie6update/images/"
    }
</script>
<script type="text/javascript" src="/ie6update/ie6update.js"></script>
<![endif]-->
</body>
</html>