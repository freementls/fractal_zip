<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Using namespaces: Aliasing/Importing - Manual</title>
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
 <link rel="index" href="language.namespaces.php" />
 <link rel="prev" href="language.namespaces.nsconstants.php" />
 <link rel="next" href="language.namespaces.global.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/namespaces.importing" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.namespaces.importing.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{VWEPZ5Z8}" />
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
 <li class="header up"><a href="language.namespaces.php">Namespaces</a></li>
 <li><a href="language.namespaces.rationale.php">Namespaces overview</a></li>
 <li><a href="language.namespaces.definition.php">Defining namespaces</a></li>
 <li><a href="language.namespaces.nested.php">Declaring sub-namespaces</a></li>
 <li><a href="language.namespaces.definitionmultiple.php">Defining multiple namespaces in the same file</a></li>
 <li><a href="language.namespaces.basics.php">Using namespaces: Basics</a></li>
 <li><a href="language.namespaces.dynamic.php">Namespaces and dynamic language features</a></li>
 <li><a href="language.namespaces.nsconstants.php">namespace keyword and __NAMESPACE__ constant</a></li>
 <li class="active"><a href="language.namespaces.importing.php">Using namespaces: Aliasing/Importing</a></li>
 <li><a href="language.namespaces.global.php">Global space</a></li>
 <li><a href="language.namespaces.fallback.php">Using namespaces: fallback to global function/constant</a></li>
 <li><a href="language.namespaces.rules.php">Name resolution rules</a></li>
 <li><a href="language.namespaces.faq.php">FAQ: things you need to know about namespaces</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.namespaces.global.php">Global space<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.namespaces.nsconstants.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />namespace keyword and __NAMESPACE__ constant</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.importing.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.namespaces.importing.php">Brazilian Portuguese</option>
    <option value="zh/language.namespaces.importing.php">Chinese (Simplified)</option>
    <option value="fr/language.namespaces.importing.php">French</option>
    <option value="de/language.namespaces.importing.php">German</option>
    <option value="ja/language.namespaces.importing.php">Japanese</option>
    <option value="pl/language.namespaces.importing.php">Polish</option>
    <option value="ro/language.namespaces.importing.php">Romanian</option>
    <option value="ru/language.namespaces.importing.php">Russian</option>
    <option value="fa/language.namespaces.importing.php">Persian</option>
    <option value="es/language.namespaces.importing.php">Spanish</option>
    <option value="tr/language.namespaces.importing.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.namespaces.importing" class="sect1">
  <h2 class="title">Using namespaces: Aliasing/Importing</h2>
  <p class="verinfo">(PHP 5 &gt;= 5.3.0)</p>
  <p class="para">
   The ability to refer to an external fully qualified name with an alias, or importing,
   is an important feature of namespaces.  This is similar to the
   ability of unix-based filesystems to create symbolic links to a file or to a directory.
  </p>
  <p class="para">
   PHP namespaces support
   three kinds of aliasing or importing: aliasing a class name, aliasing an interface name,
   and aliasing a namespace name.
   Note that importing a function or constant is not supported.
  </p>
  <p class="para">
   In PHP, aliasing is accomplished with the <em>use</em> operator.  Here
   is an example showing all 3 kinds of importing:
   <div class="example" id="example-242">
    <p><strong>Example #1 importing/aliasing with the use operator</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">;<br />use&nbsp;</span><span style="color: #0000BB">My</span><span style="color: #007700">\</span><span style="color: #0000BB">Full</span><span style="color: #007700">\</span><span style="color: #0000BB">Classname&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">Another</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;this&nbsp;is&nbsp;the&nbsp;same&nbsp;as&nbsp;use&nbsp;My\Full\NSname&nbsp;as&nbsp;NSname<br /></span><span style="color: #007700">use&nbsp;</span><span style="color: #0000BB">My</span><span style="color: #007700">\</span><span style="color: #0000BB">Full</span><span style="color: #007700">\</span><span style="color: #0000BB">NSname</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;importing&nbsp;a&nbsp;global&nbsp;class<br /></span><span style="color: #007700">use&nbsp;</span><span style="color: #0000BB">ArrayObject</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;namespace\</span><span style="color: #0000BB">Another</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;object&nbsp;of&nbsp;class&nbsp;foo\Another<br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">Another</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;object&nbsp;of&nbsp;class&nbsp;My\Full\Classname<br /></span><span style="color: #0000BB">NSname</span><span style="color: #007700">\</span><span style="color: #0000BB">subns</span><span style="color: #007700">\</span><span style="color: #0000BB">func</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;function&nbsp;My\Full\NSname\subns\func<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">ArrayObject</span><span style="color: #007700">(array(</span><span style="color: #0000BB">1</span><span style="color: #007700">));&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;object&nbsp;of&nbsp;class&nbsp;ArrayObject<br />//&nbsp;without&nbsp;the&nbsp;"use&nbsp;ArrayObject"&nbsp;we&nbsp;would&nbsp;instantiate&nbsp;an&nbsp;object&nbsp;of&nbsp;class&nbsp;foo\ArrayObject<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   Note that for namespaced names (fully qualified namespace names containing
   namespace separator, such as <em>Foo\Bar</em> as opposed to global names that
   do not, such as <em>FooBar</em>), the leading backslash is unnecessary and not
   recommended, as import names
   must be fully qualified, and are not processed relative to the current namespace.
  </p>
  <p class="para">
   PHP additionally supports a convenience shortcut to place multiple use statements
   on the same line
   <div class="example" id="example-243">
    <p><strong>Example #2 importing/aliasing with the use operator, multiple use statements combined</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">use&nbsp;</span><span style="color: #0000BB">My</span><span style="color: #007700">\</span><span style="color: #0000BB">Full</span><span style="color: #007700">\</span><span style="color: #0000BB">Classname&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">Another</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">My</span><span style="color: #007700">\</span><span style="color: #0000BB">Full</span><span style="color: #007700">\</span><span style="color: #0000BB">NSname</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">Another</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;object&nbsp;of&nbsp;class&nbsp;My\Full\Classname<br /></span><span style="color: #0000BB">NSname</span><span style="color: #007700">\</span><span style="color: #0000BB">subns</span><span style="color: #007700">\</span><span style="color: #0000BB">func</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;function&nbsp;My\Full\NSname\subns\func<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <p class="para">
   Importing is performed at compile-time, and so does not affect dynamic class, function
   or constant names.
   <div class="example" id="example-244">
    <p><strong>Example #3 Importing and dynamic names</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">use&nbsp;</span><span style="color: #0000BB">My</span><span style="color: #007700">\</span><span style="color: #0000BB">Full</span><span style="color: #007700">\</span><span style="color: #0000BB">Classname&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">Another</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">My</span><span style="color: #007700">\</span><span style="color: #0000BB">Full</span><span style="color: #007700">\</span><span style="color: #0000BB">NSname</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">Another</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;object&nbsp;of&nbsp;class&nbsp;My\Full\Classname<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Another'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;object&nbsp;of&nbsp;class&nbsp;Another<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <p class="para">
   In addition, importing only affects unqualified and qualified names.  Fully qualified
   names are absolute, and unaffected by imports.
   <div class="example" id="example-245">
    <p><strong>Example #4 Importing and fully qualified names</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">use&nbsp;</span><span style="color: #0000BB">My</span><span style="color: #007700">\</span><span style="color: #0000BB">Full</span><span style="color: #007700">\</span><span style="color: #0000BB">Classname&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">Another</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">My</span><span style="color: #007700">\</span><span style="color: #0000BB">Full</span><span style="color: #007700">\</span><span style="color: #0000BB">NSname</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">Another</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;object&nbsp;of&nbsp;class&nbsp;My\Full\Classname<br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;\</span><span style="color: #0000BB">Another</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;object&nbsp;of&nbsp;class&nbsp;Another<br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">Another</span><span style="color: #007700">\</span><span style="color: #0000BB">thing</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;object&nbsp;of&nbsp;class&nbsp;My\Full\Classname\thing<br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;\</span><span style="color: #0000BB">Another</span><span style="color: #007700">\</span><span style="color: #0000BB">thing</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;object&nbsp;of&nbsp;class&nbsp;Another\thing<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <div class="sect2" id="language.namespaces.importing.scope">
   <h3 class="title">Scoping rules for importing</h3>
   <p class="para">
    The <em>use</em> keyword must be declared in the 
    outermost scope of a file (the global scope) or inside namespace 
    declarations. This is because the importing is done at compile 
    time and not runtime, so it cannot be block scoped. The following 
    example will show an illegal use of the <em>use</em> 
    keyword:
   </p>
   <p class="para">
    <div class="example" id="example-246">
     <p><strong>Example #5 Illegal importing rule</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">Languages</span><span style="color: #007700">;<br /><br />class&nbsp;</span><span style="color: #0000BB">Greenlandic<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">Languages</span><span style="color: #007700">\</span><span style="color: #0000BB">Danish</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;...<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     Importing rules are per file basis, meaning included files will 
     <em class="emphasis">NOT</em> inherit the parent file&#039;s importing rules.
    </p>
   </p></blockquote>
  </div>
 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.namespaces.global.php">Global space<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.namespaces.nsconstants.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />namespace keyword and __NAMESPACE__ constant</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.importing.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.namespaces.importing&amp;redirect=@w{VWEPZ5Z8}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.importing&amp;redirect=@w{VWEPZ5Z8}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Using namespaces: Aliasing/Importing</strong>
 </div><div id="allnotes">
 <a name="108625"></a>
 <div class="note">
  <strong class='user'>samuel dot roze at gmail dot com</strong>
  <a href="#108625" class="date">11-May-2012 01:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
(All the backslashes in namespaces are slashes because I can't figure out how to post backslashes here.)<br />
<br />
You can have the same "use" for a class and a namespace. For example, if you have these files:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// foo/bar.php<br />
</span><span class="default">namespace foo</span><span class="keyword">;<br />
<br />
class </span><span class="default">bar<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__toString </span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'foo\bar\__toString()'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
</span><span class="comment">// foo/bar/MyClass.php<br />
</span><span class="default">namespace foo</span><span class="keyword">/</span><span class="default">bar</span><span class="keyword">;<br />
<br />
class </span><span class="default">MyClass<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__toString </span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'foo\bar\MyClass\__toString()'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
In another namespace, you can do:<br />
<span class="default">&lt;?php<br />
namespace another</span><span class="keyword">;<br />
require_once </span><span class="string">'foo/bar.php'</span><span class="keyword">;<br />
require_once </span><span class="string">'foo/bar/MyClass.php'</span><span class="keyword">;<br />
<br />
use </span><span class="default">foo</span><span class="keyword">/</span><span class="default">bar</span><span class="keyword">;<br />
<br />
</span><span class="default">$bar </span><span class="keyword">= new </span><span class="default">bar</span><span class="keyword">();<br />
echo </span><span class="default">$bar</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">$class </span><span class="keyword">= new </span><span class="default">bar</span><span class="keyword">/</span><span class="default">MyClass</span><span class="keyword">();<br />
echo </span><span class="default">$class</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
And it will makes the following output:<br />
foo\bar\__toString()<br />
foo\bar\MyClass\__toString()</span>
</code></div>
  </div>
 </div>
 <a name="105394"></a>
 <div class="note">
  <strong class='user'>c dot 1 at smithies dot org</strong>
  <a href="#105394" class="date">14-Aug-2011 03:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you are testing your code at the CLI, note that namespace aliases do not work!<br />
<br />
(Before I go on, all the backslashes in this example are changed to percent signs because I cannot get sensible results to display in the posting preview otherwise. Please mentally translate all percent signs henceforth as backslashes.)<br />
<br />
Suppose you have a class you want to test in myclass.php:<br />
<br />
<span class="default">&lt;?php<br />
namespace my</span><span class="keyword">%</span><span class="default">space</span><span class="keyword">;<br />
class </span><span class="default">myclass </span><span class="keyword">{<br />
&nbsp;</span><span class="comment">// ...<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
and you then go into the CLI to test it. You would like to think that this would work, as you type it line by line:<br />
<br />
require 'myclass.php';<br />
use my%space%myclass; // should set 'myclass' as alias for 'my%space%myclass'<br />
$x = new myclass; // FATAL ERROR<br />
<br />
I believe that this is because aliases are only resolved at compile time, whereas the CLI simply evaluates statements; so use statements are ineffective in the CLI.<br />
<br />
If you put your test code into test.php:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">require </span><span class="string">'myclass.php'</span><span class="keyword">;<br />
use </span><span class="default">my</span><span class="keyword">%</span><span class="default">space</span><span class="keyword">%</span><span class="default">myclass</span><span class="keyword">;<br />
</span><span class="default">$x </span><span class="keyword">= new </span><span class="default">myclass</span><span class="keyword">;<br />
</span><span class="comment">//...<br />
</span><span class="default">?&gt;<br />
</span>it will work fine.<br />
<br />
I hope this reduces the number of prematurely bald people.</span>
</code></div>
  </div>
 </div>
 <a name="101792"></a>
 <div class="note">
  <strong class='user'>Jan Tvrdk</strong>
  <a href="#101792" class="date">11-Jan-2011 06:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Importing and aliasing an interface name is also supported.</span>
</code></div>
  </div>
 </div>
 <a name="101199"></a>
 <div class="note">
  <strong class='user'>thinice at gmail.com</strong>
  <a href="#101199" class="date">01-Dec-2010 10:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Because imports happen at compile time, there's no polymorphism potential by embedding the use keyword in a conditonal.<br />
<br />
e.g.:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (</span><span class="default">$objType </span><span class="keyword">== </span><span class="string">'canine'</span><span class="keyword">) {<br />
&nbsp; use </span><span class="default">AnimalCanine </span><span class="keyword">as </span><span class="default">Beast</span><span class="keyword">;<br />
}<br />
if (</span><span class="default">$objType </span><span class="keyword">== </span><span class="string">'bovine'</span><span class="keyword">) {<br />
&nbsp; use </span><span class="default">AnimalBovine </span><span class="keyword">as </span><span class="default">Beast</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$oBeast </span><span class="keyword">= new </span><span class="default">Beast</span><span class="keyword">;<br />
</span><span class="default">$oBeast</span><span class="keyword">-&gt;</span><span class="default">feed</span><span class="keyword">();<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="98908"></a>
 <div class="note">
  <strong class='user'>nsdhami at live dot jp</strong>
  <a href="#98908" class="date">15-Jul-2010 02:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The "use" keyword can not be declared inside the function or method. It should be declared as global, after the "namespace" as:<br />
<br />
<span class="default">&lt;?php<br />
<br />
namespace mydir</span><span class="keyword">;<br />
<br />
</span><span class="comment">// works perfectly<br />
</span><span class="keyword">use </span><span class="default">mydir</span><span class="keyword">/</span><span class="default">subdir</span><span class="keyword">/</span><span class="default">Class1 </span><span class="keyword">as </span><span class="default">Class1</span><span class="keyword">;<br />
<br />
function </span><span class="default">fun1</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Parse error: syntax error, unexpected T_USE<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">use </span><span class="default">mydir</span><span class="keyword">/</span><span class="default">subdir</span><span class="keyword">/</span><span class="default">Class1 </span><span class="keyword">as </span><span class="default">Class1</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">Class2<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">fun2</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// Parse error: syntax error, unexpected T_USE<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">use </span><span class="default">mydir</span><span class="keyword">/</span><span class="default">subdir</span><span class="keyword">/</span><span class="default">Class1 </span><span class="keyword">as </span><span class="default">Class1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.namespaces.importing&amp;redirect=@w{VWEPZ5Z8}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.importing&amp;redirect=@w{VWEPZ5Z8}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.namespaces.importing.php">show source</a> |
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