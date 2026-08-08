<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Callbacks - Manual</title>
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
 <link rel="index" href="language.types.php" />
 <link rel="prev" href="language.types.null.php" />
 <link rel="next" href="language.pseudo-types.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/types.callable" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.types.callable.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.types.callable.php" />
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
 <li class="header up"><a href="language.types.php">Types</a></li>
 <li><a href="language.types.intro.php">Introduction</a></li>
 <li><a href="language.types.boolean.php">Booleans</a></li>
 <li><a href="language.types.integer.php">Integers</a></li>
 <li><a href="language.types.float.php">Floating point numbers</a></li>
 <li><a href="language.types.string.php">Strings</a></li>
 <li><a href="language.types.array.php">Arrays</a></li>
 <li><a href="language.types.object.php">Objects</a></li>
 <li><a href="language.types.resource.php">Resources</a></li>
 <li><a href="language.types.null.php">NULL</a></li>
 <li class="active"><a href="language.types.callable.php">Callbacks</a></li>
 <li><a href="language.pseudo-types.php">Pseudo-types and variables used in this documentation</a></li>
 <li><a href="language.types.type-juggling.php">Type Juggling</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.pseudo-types.php">Pseudo-types and variables used in this documentation<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.null.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />NULL</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.callable.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.types.callable.php">Brazilian Portuguese</option>
    <option value="zh/language.types.callable.php">Chinese (Simplified)</option>
    <option value="fr/language.types.callable.php">French</option>
    <option value="de/language.types.callable.php">German</option>
    <option value="ja/language.types.callable.php">Japanese</option>
    <option value="pl/language.types.callable.php">Polish</option>
    <option value="ro/language.types.callable.php">Romanian</option>
    <option value="ru/language.types.callable.php">Russian</option>
    <option value="fa/language.types.callable.php">Persian</option>
    <option value="es/language.types.callable.php">Spanish</option>
    <option value="tr/language.types.callable.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.types.callable" class="sect1">
 <h2 class="title">Callbacks</h2>
 
 <p class="para">
  Callbacks can be denoted by <span class="type"><a href="language.types.callable.php" class="type callable">callable</a></span> type hint as of PHP 5.4.
  This documentation used <span class="type"><a href="language.pseudo-types.php#language.types.callback" class="type callback">callback</a></span> type information for the same
  purpose.
 </p>

 <p class="para">
  Some functions like  <span class="function"><a href="function.call-user-func.php" class="function">call_user_func()</a></span> or
   <span class="function"><a href="function.usort.php" class="function">usort()</a></span> accept user-defined callback functions as a
  parameter. Callback functions can not only be simple functions, but also
  <span class="type"><a href="language.types.object.php" class="type object">object</a></span> methods, including static class methods.
 </p>

 <div class="sect2" id="language.types.callable.passing">
  <h3 class="title">Passing</h3>

  <p class="para">
   A PHP function is passed by its name as a <span class="type"><a href="language.types.string.php" class="type string">string</a></span>. Any built-in
   or user-defined function can be used, except language constructs such as:
    <span class="function"><a href="function.array.php" class="function">array()</a></span>,  <span class="function"><a href="function.echo.php" class="function">echo</a></span>,
    <span class="function"><a href="function.empty.php" class="function">empty()</a></span>,  <span class="function"><a href="function.eval.php" class="function">eval()</a></span>, 
    <span class="function"><a href="function.exit.php" class="function">exit()</a></span>,  <span class="function"><a href="function.isset.php" class="function">isset()</a></span>, 
    <span class="function"><a href="function.list.php" class="function">list()</a></span>,  <span class="function"><a href="function.print.php" class="function">print</a></span> or
    <span class="function"><a href="function.unset.php" class="function">unset()</a></span>.
  </p>

  <p class="para">
   A method of an instantiated <span class="type"><a href="language.types.object.php" class="type object">object</a></span> is passed as an
   <span class="type"><a href="language.types.array.php" class="type array">array</a></span> containing an <span class="type"><a href="language.types.object.php" class="type object">object</a></span> at index 0 and the
   method name at index 1.
  </p>

  <p class="para">
   Static class methods can also be passed without instantiating an
   <span class="type"><a href="language.types.object.php" class="type object">object</a></span> of that class by passing the class name instead of an
   <span class="type"><a href="language.types.object.php" class="type object">object</a></span> at index 0.
   As of PHP 5.2.3, it is also possible to pass
   <em>&#039;ClassName::methodName&#039;</em>.
  </p>

  <p class="para">
   Apart from common user-defined function,  <span class="function"><a href="function.create-function.php" class="function">create_function()</a></span>
   can also be used to create an anonymous callback function. As of PHP 5.3.0 
   it is possible to also pass a
   <a href="functions.anonymous.php" class="link">closure</a> to a callback parameter.
  </p>

  <p class="para">
   <div class="example" id="example-95">
    <p><strong>Example #1 
     Callback function examples
    </strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php&nbsp;<br /><br /></span><span style="color: #FF8000">//&nbsp;An&nbsp;example&nbsp;callback&nbsp;function<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">my_callback_function</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'hello&nbsp;world!'</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;An&nbsp;example&nbsp;callback&nbsp;method<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyClass&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;static&nbsp;function&nbsp;</span><span style="color: #0000BB">myCallbackMethod</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'Hello&nbsp;World!'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;Type&nbsp;1:&nbsp;Simple&nbsp;callback<br /></span><span style="color: #0000BB">call_user_func</span><span style="color: #007700">(</span><span style="color: #DD0000">'my_callback_function'</span><span style="color: #007700">);&nbsp;<br /><br /></span><span style="color: #FF8000">//&nbsp;Type&nbsp;2:&nbsp;Static&nbsp;class&nbsp;method&nbsp;call<br /></span><span style="color: #0000BB">call_user_func</span><span style="color: #007700">(array(</span><span style="color: #DD0000">'MyClass'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'myCallbackMethod'</span><span style="color: #007700">));&nbsp;<br /><br /></span><span style="color: #FF8000">//&nbsp;Type&nbsp;3:&nbsp;Object&nbsp;method&nbsp;call<br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">call_user_func</span><span style="color: #007700">(array(</span><span style="color: #0000BB">$obj</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'myCallbackMethod'</span><span style="color: #007700">));<br /><br /></span><span style="color: #FF8000">//&nbsp;Type&nbsp;4:&nbsp;Static&nbsp;class&nbsp;method&nbsp;call&nbsp;(As&nbsp;of&nbsp;PHP&nbsp;5.2.3)<br /></span><span style="color: #0000BB">call_user_func</span><span style="color: #007700">(</span><span style="color: #DD0000">'MyClass::myCallbackMethod'</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;Type&nbsp;5:&nbsp;Relative&nbsp;static&nbsp;class&nbsp;method&nbsp;call&nbsp;(As&nbsp;of&nbsp;PHP&nbsp;5.3.0)<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">A&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;static&nbsp;function&nbsp;</span><span style="color: #0000BB">who</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"A\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">B&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">A&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;static&nbsp;function&nbsp;</span><span style="color: #0000BB">who</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"B\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">call_user_func</span><span style="color: #007700">(array(</span><span style="color: #DD0000">'B'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'parent::who'</span><span style="color: #007700">));&nbsp;</span><span style="color: #FF8000">//&nbsp;A<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <p class="para">
   <div class="example" id="example-96">
    <p><strong>Example #2 
     Callback example using a Closure
    </strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">//&nbsp;Our&nbsp;closure<br /></span><span style="color: #0000BB">$double&nbsp;</span><span style="color: #007700">=&nbsp;function(</span><span style="color: #0000BB">$a</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">*&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">;<br />};<br /><br /></span><span style="color: #FF8000">//&nbsp;This&nbsp;is&nbsp;our&nbsp;range&nbsp;of&nbsp;numbers<br /></span><span style="color: #0000BB">$numbers&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">range</span><span style="color: #007700">(</span><span style="color: #0000BB">1</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">5</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;Use&nbsp;the&nbsp;closure&nbsp;as&nbsp;a&nbsp;callback&nbsp;here&nbsp;to&nbsp;<br />//&nbsp;double&nbsp;the&nbsp;size&nbsp;of&nbsp;each&nbsp;element&nbsp;in&nbsp;our&nbsp;<br />//&nbsp;range<br /></span><span style="color: #0000BB">$new_numbers&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">array_map</span><span style="color: #007700">(</span><span style="color: #0000BB">$double</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$numbers</span><span style="color: #007700">);<br /><br />print&nbsp;</span><span style="color: #0000BB">implode</span><span style="color: #007700">(</span><span style="color: #DD0000">'&nbsp;'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$new_numbers</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
2 4 6 8 10
</pre></div>
    </div>
   </div>
  </p>

  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <span class="simpara">
    In PHP 4, it was necessary to use a reference to create a callback that
    points to the actual <span class="type"><a href="language.types.object.php" class="type object">object</a></span>, and not a copy of it. For more
    details, see
    <a href="language.references.php" class="link">References Explained</a>.
   </span>
  </p></blockquote>

  <blockquote class="note"><p><strong class="note">Note</strong>: <p class="para">Callbacks registered
with functions such as  <span class="function"><a href="function.call-user-func.php" class="function">call_user_func()</a></span> and  <span class="function"><a href="function.call-user-func-array.php" class="function">call_user_func_array()</a></span> will not be
called if there is an uncaught exception thrown in a previous callback.</p></p></blockquote>
 </div>

</div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.types.callable&amp;redirect=http://www.php.net/manual/en/language.types.callable.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types.callable&amp;redirect=http://www.php.net/manual/en/language.types.callable.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Callbacks</strong>
 </div><div id="allnotes">
 <a name="109073"></a>
 <div class="note">
  <strong class='user'>andrewbessa at gmail dot com</strong>
  <a href="#109073" class="date">19-Jun-2012 03:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can also use the $this variable to specify a callback:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">MyClass </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$property </span><span class="keyword">= </span><span class="string">'Hello World!'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">MyMethod</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">call_user_func</span><span class="keyword">(array(</span><span class="default">$this</span><span class="keyword">, </span><span class="string">'myCallbackMethod'</span><span class="keyword">));<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">MyCallbackMethod</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">property</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.types.callable&amp;redirect=http://www.php.net/manual/en/language.types.callable.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types.callable&amp;redirect=http://www.php.net/manual/en/language.types.callable.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.types.callable.php">show source</a> |
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