<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Using namespaces: Basics - Manual</title>
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
 <link rel="prev" href="language.namespaces.definitionmultiple.php" />
 <link rel="next" href="language.namespaces.dynamic.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/namespaces.basics" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.namespaces.basics.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{GNGRJJCH}" />
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
 <li class="active"><a href="language.namespaces.basics.php">Using namespaces: Basics</a></li>
 <li><a href="language.namespaces.dynamic.php">Namespaces and dynamic language features</a></li>
 <li><a href="language.namespaces.nsconstants.php">namespace keyword and __NAMESPACE__ constant</a></li>
 <li><a href="language.namespaces.importing.php">Using namespaces: Aliasing/Importing</a></li>
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
  <a href="language.namespaces.dynamic.php">Namespaces and dynamic language features<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.namespaces.definitionmultiple.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Defining multiple namespaces in the same file</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.basics.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.namespaces.basics.php">Brazilian Portuguese</option>
    <option value="zh/language.namespaces.basics.php">Chinese (Simplified)</option>
    <option value="fr/language.namespaces.basics.php">French</option>
    <option value="de/language.namespaces.basics.php">German</option>
    <option value="ja/language.namespaces.basics.php">Japanese</option>
    <option value="pl/language.namespaces.basics.php">Polish</option>
    <option value="ro/language.namespaces.basics.php">Romanian</option>
    <option value="ru/language.namespaces.basics.php">Russian</option>
    <option value="fa/language.namespaces.basics.php">Persian</option>
    <option value="es/language.namespaces.basics.php">Spanish</option>
    <option value="tr/language.namespaces.basics.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.namespaces.basics" class="sect1">
  <h2 class="title">Using namespaces: Basics</h2>
  <p class="verinfo">(PHP 5 &gt;= 5.3.0)</p>
  <p class="para">
   Before discussing the use of namespaces, it is important to understand how PHP
   knows which namespaced element your code is requesting.  A simple analogy can be made
   between PHP namespaces and a filesystem.  There are three ways to access a file in a
   file system:
   <ol type="1">
    <li class="listitem">
     <span class="simpara">
      Relative file name like <em>foo.txt</em>.  This resolves to
      <em>currentdirectory/foo.txt</em> where currentdirectory is the
      directory currently occupied.  So if the current directory is
      <em>/home/foo</em>, the name resolves to <em>/home/foo/foo.txt</em>.
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      Relative path name like <em>subdirectory/foo.txt</em>.  This resolves
      to <em>currentdirectory/subdirectory/foo.txt</em>.
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      Absolute path name like <em>/main/foo.txt</em>.  This resolves
      to <em>/main/foo.txt</em>.
     </span>
    </li>
   </ol>
   The same principle can be applied to namespaced elements in PHP.  For
   example, a class name can be referred to in three ways:
   <ol type="1">
    <li class="listitem">
     <span class="simpara">
      Unqualified name, or an unprefixed class name like
      <em>$a = new foo();</em> or
      <em>foo::staticmethod();</em>.  If the current namespace is
      <em>currentnamespace</em>, this resolves to
      <em>currentnamespace\foo</em>.  If
      the code is global, non-namespaced code, this resolves to <em>foo</em>.
     </span>
     <span class="simpara">
      One caveat: unqualified names for functions and constants will
      resolve to global functions and constants if the namespaced function or constant
      is not defined.  See <a href="language.namespaces.fallback.php" class="link">Using namespaces:
      fallback to global function/constant</a> for details.
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      Qualified name, or a prefixed class name like
      <em>$a = new subnamespace\foo();</em> or
      <em>subnamespace\foo::staticmethod();</em>.  If the current namespace is
      <em>currentnamespace</em>, this resolves to
      <em>currentnamespace\subnamespace\foo</em>.  If
      the code is global, non-namespaced code, this resolves to <em>subnamespace\foo</em>.
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      Fully qualified name, or a prefixed name with global prefix operator like
      <em>$a = new \currentnamespace\foo();</em> or
      <em>\currentnamespace\foo::staticmethod();</em>.  This always resolves
      to the literal name specified in the code, <em>currentnamespace\foo</em>.
     </span>
    </li>
   </ol>
  </p>
  <p class="para">
   Here is an example of the three kinds of syntax in actual code:
   <div class="informalexample">
    <p class="simpara">file1.php</p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">Foo</span><span style="color: #007700">\</span><span style="color: #0000BB">Bar</span><span style="color: #007700">\</span><span style="color: #0000BB">subnamespace</span><span style="color: #007700">;<br /><br />const&nbsp;</span><span style="color: #0000BB">FOO&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">()&nbsp;{}<br />class&nbsp;</span><span style="color: #0000BB">foo<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;static&nbsp;function&nbsp;</span><span style="color: #0000BB">staticmethod</span><span style="color: #007700">()&nbsp;{}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <p class="simpara">file2.php</p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">Foo</span><span style="color: #007700">\</span><span style="color: #0000BB">Bar</span><span style="color: #007700">;<br />include&nbsp;</span><span style="color: #DD0000">'file1.php'</span><span style="color: #007700">;<br /><br />const&nbsp;</span><span style="color: #0000BB">FOO&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">;<br />function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">()&nbsp;{}<br />class&nbsp;</span><span style="color: #0000BB">foo<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;static&nbsp;function&nbsp;</span><span style="color: #0000BB">staticmethod</span><span style="color: #007700">()&nbsp;{}<br />}<br /><br /></span><span style="color: #FF8000">/*&nbsp;Unqualified&nbsp;name&nbsp;*/<br /></span><span style="color: #0000BB">foo</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;resolves&nbsp;to&nbsp;function&nbsp;Foo\Bar\foo<br /></span><span style="color: #0000BB">foo</span><span style="color: #007700">::</span><span style="color: #0000BB">staticmethod</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;resolves&nbsp;to&nbsp;class&nbsp;Foo\Bar\foo,&nbsp;method&nbsp;staticmethod<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">FOO</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;resolves&nbsp;to&nbsp;constant&nbsp;Foo\Bar\FOO<br /><br />/*&nbsp;Qualified&nbsp;name&nbsp;*/<br /></span><span style="color: #0000BB">subnamespace</span><span style="color: #007700">\</span><span style="color: #0000BB">foo</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;resolves&nbsp;to&nbsp;function&nbsp;Foo\Bar\subnamespace\foo<br /></span><span style="color: #0000BB">subnamespace</span><span style="color: #007700">\</span><span style="color: #0000BB">foo</span><span style="color: #007700">::</span><span style="color: #0000BB">staticmethod</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;resolves&nbsp;to&nbsp;class&nbsp;Foo\Bar\subnamespace\foo,<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;//&nbsp;method&nbsp;staticmethod<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">subnamespace</span><span style="color: #007700">\</span><span style="color: #0000BB">FOO</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;resolves&nbsp;to&nbsp;constant&nbsp;Foo\Bar\subnamespace\FOO<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br />/*&nbsp;Fully&nbsp;qualified&nbsp;name&nbsp;*/<br /></span><span style="color: #007700">\</span><span style="color: #0000BB">Foo</span><span style="color: #007700">\</span><span style="color: #0000BB">Bar</span><span style="color: #007700">\</span><span style="color: #0000BB">foo</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;resolves&nbsp;to&nbsp;function&nbsp;Foo\Bar\foo<br /></span><span style="color: #007700">\</span><span style="color: #0000BB">Foo</span><span style="color: #007700">\</span><span style="color: #0000BB">Bar</span><span style="color: #007700">\</span><span style="color: #0000BB">foo</span><span style="color: #007700">::</span><span style="color: #0000BB">staticmethod</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;resolves&nbsp;to&nbsp;class&nbsp;Foo\Bar\foo,&nbsp;method&nbsp;staticmethod<br /></span><span style="color: #007700">echo&nbsp;\</span><span style="color: #0000BB">Foo</span><span style="color: #007700">\</span><span style="color: #0000BB">Bar</span><span style="color: #007700">\</span><span style="color: #0000BB">FOO</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;resolves&nbsp;to&nbsp;constant&nbsp;Foo\Bar\FOO<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <p class="para">
   Note that to access any global
   class, function or constant, a fully qualified name can be used, such as
    <span class="function"><strong>\strlen()</strong></span> or <strong class="classname">\Exception</strong> or
   <em>\INI_ALL</em>.
   <div class="example" id="example-234">
    <p><strong>Example #1 Accessing global classes, functions and constants from within a namespace</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">Foo</span><span style="color: #007700">;<br /><br />function&nbsp;</span><span style="color: #0000BB">strlen</span><span style="color: #007700">()&nbsp;{}<br />const&nbsp;</span><span style="color: #0000BB">INI_ALL&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">3</span><span style="color: #007700">;<br />class&nbsp;</span><span style="color: #0000BB">Exception&nbsp;</span><span style="color: #007700">{}<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;\</span><span style="color: #0000BB">strlen</span><span style="color: #007700">(</span><span style="color: #DD0000">'hi'</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;global&nbsp;function&nbsp;strlen<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;\</span><span style="color: #0000BB">INI_ALL</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;accesses&nbsp;global&nbsp;constant&nbsp;INI_ALL<br /></span><span style="color: #0000BB">$c&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;\</span><span style="color: #0000BB">Exception</span><span style="color: #007700">(</span><span style="color: #DD0000">'error'</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;global&nbsp;class&nbsp;Exception<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.namespaces.dynamic.php">Namespaces and dynamic language features<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.namespaces.definitionmultiple.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Defining multiple namespaces in the same file</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.basics.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.namespaces.basics&amp;redirect=@w{GNGRJJCH}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.basics&amp;redirect=@w{GNGRJJCH}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Using namespaces: Basics</strong>
 </div><div id="allnotes">
 <a name="107684"></a>
 <div class="note">
  <strong class='user'>tom at tomwardrop dot com</strong>
  <a href="#107684" class="date">26-Feb-2012 10:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It seems the file system analogy only goes so far. One thing that's missing that would be very useful is relative navigation up the namespace chain, e.g.<br />
<br />
<span class="default">&lt;?php<br />
namespace MyProject </span><span class="keyword">{<br />
&nbsp;&nbsp; class </span><span class="default">Person </span><span class="keyword">{}<br />
}<br />
<br />
</span><span class="default">namespace MyProjectPeople </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; class </span><span class="default">Adult </span><span class="keyword">extends ..</span><span class="default">Person </span><span class="keyword">{}<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
That would be really nice, especially if you had really deep namespaces. It would save you having to type out the full namespace just to reference a resource one level up.</span>
</code></div>
  </div>
 </div>
 <a name="106777"></a>
 <div class="note">
  <strong class='user'>Lukas Z</strong>
  <a href="#106777" class="date">05-Dec-2011 03:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Well variables inside namespaces do not override others since variables are never affected by namespace but always global:<br />
"Although any valid PHP code can be contained within a namespace, only four types of code are affected by namespaces: classes, interfaces, functions and constants. "<br />
<br />
Source: "Defining Namespaces"<br />
<a href="@w{J627WNRP}" rel="nofollow" target="_blank">@w{J627WNRP}</a></span>
</code></div>
  </div>
 </div>
 <a name="104411"></a>
 <div class="note">
  <strong class='user'>philip dot preisser at arcor dot de</strong>
  <a href="#104411" class="date">14-Jun-2011 02:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Working with variables can overwrite equal variables in other namespaces<br />
<br />
<span class="default">&lt;?php </span><span class="comment">// php5 - package-version : 5.3.5-1ubuntu7.2<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">namespace<br />
&nbsp;&nbsp;&nbsp; main<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{}<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">namespace<br />
&nbsp;&nbsp;&nbsp; mainsub1<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$data </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">namespace<br />
&nbsp;&nbsp;&nbsp; mainsub2<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$data</span><span class="keyword">;</span><span class="comment">// 1<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$data </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">namespace<br />
&nbsp;&nbsp;&nbsp; mainsub1<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$data</span><span class="keyword">;</span><span class="comment">// 2<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$data </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">namespace<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$data</span><span class="keyword">;</span><span class="comment">// 1<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="101520"></a>
 <div class="note">
  <strong class='user'>Franois Vespa</strong>
  <a href="#101520" class="date">21-Dec-2010 05:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It seems you cannot nest a constant declaration within a if statement<br />
<br />
<span class="default">&lt;?php<br />
<br />
namespace FOO</span><span class="keyword">;<br />
<br />
if(eval)<br />
const </span><span class="default">BAR</span><span class="keyword">=</span><span class="default">true</span><span class="keyword">; <br />
<br />
</span><span class="comment">// will throw the following error:<br />
// PHP Parse error:&nbsp; syntax error, unexpected T_CONST<br />
<br />
// instead use:<br />
<br />
</span><span class="keyword">if(eval)<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'FOO\BAR'</span><span class="keyword">,</span><span class="default">true</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="101200"></a>
 <div class="note">
  <strong class='user'>thinice at gmail.com</strong>
  <a href="#101200" class="date">01-Dec-2010 10:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Unfortunately as of 5.3.3, it's not possible to do something like:<br />
<br />
<span class="default">&lt;?php<br />
<br />
namespace Animal </span><span class="keyword">{<br />
&nbsp; require </span><span class="string">'Bovine.php'</span><span class="keyword">;<br />
&nbsp; require </span><span class="string">'Canine.php'</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Which could be quite handy for&nbsp; a handful of reasons.</span>
</code></div>
  </div>
 </div>
 <a name="86420"></a>
 <div class="note">
  <strong class='user'>kukoman at pobox dot sk</strong>
  <a href="#86420" class="date">17-Oct-2008 11:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP 5.3.0alpha2 (cli)<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//&nbsp; namespace MyProject\DB;<br />
</span><span class="keyword">require </span><span class="string">'db.php'</span><span class="keyword">;<br />
<br />
use </span><span class="default">MyProjectDB</span><span class="keyword">; </span><span class="comment">// fine; same as DB\<br />
</span><span class="keyword">use </span><span class="default">MyProjectDBConnection </span><span class="keyword">as </span><span class="default">DBC</span><span class="keyword">; </span><span class="comment">// fine<br />
</span><span class="keyword">use </span><span class="default">MyProjectDB </span><span class="keyword">as </span><span class="default">HM</span><span class="keyword">; </span><span class="comment">// fine<br />
</span><span class="keyword">use </span><span class="default">HMConnection </span><span class="keyword">as </span><span class="default">DBC2</span><span class="keyword">; </span><span class="comment">// class call ends with FATAL!!!<br />
<br />
</span><span class="default">$x </span><span class="keyword">= new </span><span class="default">DBC</span><span class="keyword">(); </span><span class="comment">// fine<br />
</span><span class="default">$y </span><span class="keyword">= new </span><span class="default">HMConnection</span><span class="keyword">(); </span><span class="comment">// fine<br />
</span><span class="default">$z </span><span class="keyword">= new </span><span class="default">DBC2</span><span class="keyword">(); </span><span class="comment">// Fatal error: Class 'HM\Connection' not found<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="82088"></a>
 <div class="note">
  <strong class='user'>richard at richard-sumilang dot com</strong>
  <a href="#82088" class="date">27-Mar-2008 01:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Syntax for extending classes in namespaces is still the same.<br />
<br />
Lets call this Object.php:<br />
<br />
<span class="default">&lt;?php<br />
<br />
namespace comrsumilangcommon</span><span class="keyword">;<br />
<br />
class </span><span class="default">Object</span><span class="keyword">{<br />
&nbsp;&nbsp; </span><span class="comment">// ... code ...<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
And now lets create a class called String that extends object in String.php:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">String </span><span class="keyword">extends </span><span class="default">comrsumilangcommonObject</span><span class="keyword">{<br />
&nbsp;&nbsp; </span><span class="comment">// ... code ...<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Now if you class String was defined in the same namespace as Object then you don't have to specify a full namespace path:<br />
<br />
<span class="default">&lt;?php<br />
<br />
namespace comrsumilangcommon</span><span class="keyword">;<br />
<br />
class </span><span class="default">String </span><span class="keyword">extends </span><span class="default">Object<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; </span><span class="comment">// ... code ...<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Lastly, you can also alias a namespace name to use a shorter name for the class you are extending incase your class is in seperate namespace:<br />
<br />
<span class="default">&lt;?php<br />
<br />
namespace comrsumilangutil</span><span class="keyword">;<br />
use </span><span class="default">comrsumlangcommon </span><span class="keyword">as </span><span class="default">Common</span><span class="keyword">;<br />
<br />
class </span><span class="default">String </span><span class="keyword">extends </span><span class="default">CommonObject<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; </span><span class="comment">// ... code ...<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
- Richard Sumilang</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.namespaces.basics&amp;redirect=@w{GNGRJJCH}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.basics&amp;redirect=@w{GNGRJJCH}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.namespaces.basics.php">show source</a> |
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