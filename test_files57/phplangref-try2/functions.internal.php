<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Internal (built-in) functions - Manual</title>
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
 <link rel="index" href="language.functions.php" />
 <link rel="prev" href="functions.variable-functions.php" />
 <link rel="next" href="functions.anonymous.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/functions.internal" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/functions.internal.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/functions.internal.php" />
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
 <li class="header up"><a href="language.functions.php">Functions</a></li>
 <li><a href="functions.user-defined.php">User-defined functions</a></li>
 <li><a href="functions.arguments.php">Function arguments</a></li>
 <li><a href="functions.returning-values.php">Returning values</a></li>
 <li><a href="functions.variable-functions.php">Variable functions</a></li>
 <li class="active"><a href="functions.internal.php">Internal (built-in) functions</a></li>
 <li><a href="functions.anonymous.php">Anonymous functions</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="functions.anonymous.php">Anonymous functions<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="functions.variable-functions.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Variable functions</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/functions.internal.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/functions.internal.php">Brazilian Portuguese</option>
    <option value="zh/functions.internal.php">Chinese (Simplified)</option>
    <option value="fr/functions.internal.php">French</option>
    <option value="de/functions.internal.php">German</option>
    <option value="ja/functions.internal.php">Japanese</option>
    <option value="pl/functions.internal.php">Polish</option>
    <option value="ro/functions.internal.php">Romanian</option>
    <option value="ru/functions.internal.php">Russian</option>
    <option value="fa/functions.internal.php">Persian</option>
    <option value="es/functions.internal.php">Spanish</option>
    <option value="tr/functions.internal.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="functions.internal" class="sect1">
   <h2 class="title">Internal (built-in) functions</h2>
   
   <p class="para">
    PHP comes standard with many functions and constructs. There are also
    functions that require specific PHP extensions compiled in, otherwise 
    fatal &quot;undefined function&quot; errors will appear. For example, to use 
    <a href="ref.image.php" class="link">image</a> functions such as 
     <span class="function"><a href="function.imagecreatetruecolor.php" class="function">imagecreatetruecolor()</a></span>, PHP must be compiled with
    <span class="productname">GD</span> support. Or, to use
     <span class="function"><a href="function.mysql-connect.php" class="function">mysql_connect()</a></span>, PHP must be compiled with
    <a href="ref.mysql.php" class="link">MySQL</a> support. There are many core functions
    that are included in every version of PHP, such as the
    <a href="ref.strings.php" class="link">string</a> and 
    <a href="ref.var.php" class="link">variable</a> functions. A call 
    to  <span class="function"><a href="function.phpinfo.php" class="function">phpinfo()</a></span> or
     <span class="function"><a href="function.get-loaded-extensions.php" class="function">get_loaded_extensions()</a></span> will show which extensions are
    loaded into PHP. Also note that many extensions are enabled by default and
    that the PHP manual is split up by extension. See the
    <a href="configuration.php" class="link">configuration</a>,
    <a href="install.php" class="link">installation</a>, and individual
    extension chapters, for information on how to set up PHP.
   </p>
   <p class="para">
    Reading and understanding a function&#039;s prototype is explained within the
    manual section titled <a href="about.prototypes.php" class="link">how to read a
    function definition</a>. It&#039;s important to realize what a function
    returns or if a function works directly on a passed in value. For example,
     <span class="function"><a href="function.str-replace.php" class="function">str_replace()</a></span> will return the modified string while 
     <span class="function"><a href="function.usort.php" class="function">usort()</a></span> works on the actual passed in variable
    itself. Each manual page also has specific information for each
    function like information on function parameters, behavior changes,
    return values for both success and failure, and availability information.
    Knowing these important (yet often subtle) differences is crucial for
    writing correct PHP code.
   </p>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <span class="simpara">
     If the parameters given to a function are not what it expects, such as 
     passing an <span class="type"><a href="language.types.array.php" class="type array">array</a></span> where a <span class="type"><a href="language.types.string.php" class="type string">string</a></span> is expected, 
     the return value of the function is undefined. In this case it will
     likely return <strong><code>NULL</code></strong> but this is just a convention, and cannot be relied 
     upon.
    </span>
   </p></blockquote>
   <p class="para">
    See also  <span class="function"><a href="function.function-exists.php" class="function">function_exists()</a></span>, 
    <a href="funcref.php" class="link">the function reference</a>,
     <span class="function"><a href="function.get-extension-funcs.php" class="function">get_extension_funcs()</a></span>, and 
     <span class="function"><a href="function.dl.php" class="function">dl()</a></span>.
   </p>
  </div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=functions.internal&amp;redirect=http://www.php.net/manual/en/functions.internal.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=functions.internal&amp;redirect=http://www.php.net/manual/en/functions.internal.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Internal (built-in) functions</strong>
 </div>
 <div class="note">There are no user contributed notes for this page.</div></div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/functions.internal.php">show source</a> |
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