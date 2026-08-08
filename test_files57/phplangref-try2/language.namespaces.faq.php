<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: FAQ: things you need to know about namespaces - Manual</title>
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
 <link rel="prev" href="language.namespaces.rules.php" />
 <link rel="next" href="language.exceptions.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/namespaces.faq" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.namespaces.faq.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.namespaces.faq.php" />
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
 <li><a href="language.namespaces.importing.php">Using namespaces: Aliasing/Importing</a></li>
 <li><a href="language.namespaces.global.php">Global space</a></li>
 <li><a href="language.namespaces.fallback.php">Using namespaces: fallback to global function/constant</a></li>
 <li><a href="language.namespaces.rules.php">Name resolution rules</a></li>
 <li class="active"><a href="language.namespaces.faq.php">FAQ: things you need to know about namespaces</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.exceptions.php">Exceptions<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.namespaces.rules.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Name resolution rules</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.faq.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.namespaces.faq.php">Brazilian Portuguese</option>
    <option value="zh/language.namespaces.faq.php">Chinese (Simplified)</option>
    <option value="fr/language.namespaces.faq.php">French</option>
    <option value="de/language.namespaces.faq.php">German</option>
    <option value="ja/language.namespaces.faq.php">Japanese</option>
    <option value="pl/language.namespaces.faq.php">Polish</option>
    <option value="ro/language.namespaces.faq.php">Romanian</option>
    <option value="ru/language.namespaces.faq.php">Russian</option>
    <option value="fa/language.namespaces.faq.php">Persian</option>
    <option value="es/language.namespaces.faq.php">Spanish</option>
    <option value="tr/language.namespaces.faq.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.namespaces.faq" class="sect1">
  <h2 class="title">FAQ: things you need to know about namespaces</h2>
  <p class="verinfo">(PHP 5 &gt;= 5.3.0)</p>
  <p class="para">
   This FAQ is split into two sections: common questions, and some specifics of
   implementation that are helpful to understand fully.
  </p>
  <p class="para">
   First, the common questions.
   <ol type="1">
    <li class="listitem">
     <span class="simpara">
      <a href="language.namespaces.faq.php#language.namespaces.faq.shouldicare" class="link">If I don&#039;t use namespaces, should
      I care about any of this?</a>
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      <a href="language.namespaces.faq.php#language.namespaces.faq.globalclass" class="link">How do I use internal or global
      classes in a namespace?</a>
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      <a href="language.namespaces.faq.php#language.namespaces.faq.innamespace" class="link">How do I use namespaces classes
      functions, or constants in their own namespace?</a>
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      <a href="language.namespaces.faq.php#language.namespaces.faq.full" class="link">
       How does a name like <em>\my\name</em> or <em>\name</em>
       resolve?
      </a>
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      <a href="language.namespaces.faq.php#language.namespaces.faq.qualified" class="link">How does a name like
      <em>my\name</em> resolve?</a>
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      <a href="language.namespaces.faq.php#language.namespaces.faq.shortname1" class="link">How does an unqualified class name
      like <em>name</em> resolve?</a>
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      <a href="language.namespaces.faq.php#language.namespaces.faq.shortname2" class="link">How does an unqualified function
      name or unqualified constant name
      like <em>name</em> resolve?</a>
     </span>
    </li>
   </ol>
  </p>
  <p class="para">
   There are a few implementation details of the namespace implementations
   that are helpful to understand.
   <ol type="1">
    <li class="listitem">
     <span class="simpara">
      <a href="language.namespaces.faq.php#language.namespaces.faq.conflict" class="link">Import names cannot conflict with
      classes defined in the same file.</a>
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      <a href="language.namespaces.faq.php#language.namespaces.faq.nested" class="link">Nested namespaces are not allowed.
      </a>
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      <a href="language.namespaces.faq.php#language.namespaces.faq.nofuncconstantuse" class="link">Neither functions nor
      constants can be imported via the <em>use</em>
      statement.</a>
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      <a href="language.namespaces.faq.php#language.namespaces.faq.quote" class="link">Dynamic namespace names (quoted
      identifiers) should escape backslash.</a>
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      <a href="language.namespaces.faq.php#language.namespaces.faq.constants" class="link">Undefined Constants referenced
      using any backslash die with fatal error</a>
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      <a href="language.namespaces.faq.php#language.namespaces.faq.builtinconst" class="link">Cannot override special
      constants NULL, TRUE, FALSE, ZEND_THREAD_SAFE or ZEND_DEBUG_BUILD</a>
     </span>
    </li>
   </ol>
  </p>
  <div class="sect2" id="language.namespaces.faq.shouldicare">
   <h3 class="title">If I don&#039;t use namespaces, should I care about any of this?</h3>
   <p class="para">
    No.  Namespaces do not affect any existing code in any way, or any
    as-yet-to-be-written code that does not contain namespaces.  You can
    write this code if you wish:
   </p>
   <p class="para">
    <div class="example" id="example-251">
     <p><strong>Example #1 Accessing global classes outside a namespace</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;\</span><span style="color: #0000BB">stdClass</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    This is functionally equivalent to:
   </p>
   <p class="para">
    <div class="example" id="example-252">
     <p><strong>Example #2 Accessing global classes outside a namespace</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">stdClass</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div>
  <div class="sect2" id="language.namespaces.faq.globalclass">
   <h3 class="title">How do I use internal or global classes in a namespace?</h3>
   <p class="para">
    <div class="example" id="example-253">
     <p><strong>Example #3 Accessing internal classes in namespaces</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;\</span><span style="color: #0000BB">stdClass</span><span style="color: #007700">;<br /><br />function&nbsp;</span><span style="color: #0000BB">test</span><span style="color: #007700">(\</span><span style="color: #0000BB">ArrayObject&nbsp;$typehintexample&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">null</span><span style="color: #007700">)&nbsp;{}<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;\</span><span style="color: #0000BB">DirectoryIterator</span><span style="color: #007700">::</span><span style="color: #0000BB">CURRENT_AS_FILEINFO</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;extending&nbsp;an&nbsp;internal&nbsp;or&nbsp;global&nbsp;class<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyException&nbsp;</span><span style="color: #007700">extends&nbsp;\</span><span style="color: #0000BB">Exception&nbsp;</span><span style="color: #007700">{}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div>
  <div class="sect2" id="language.namespaces.faq.innamespace">
   <h3 class="title">
    How do I use namespaces classes, functions, or constants in their own
    namespace?
   </h3>
   <p class="para">
    <div class="example" id="example-254">
     <p><strong>Example #4 Accessing internal classes, functions or constants in namespaces</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">;<br /><br />class&nbsp;</span><span style="color: #0000BB">MyClass&nbsp;</span><span style="color: #007700">{}<br /><br /></span><span style="color: #FF8000">//&nbsp;using&nbsp;a&nbsp;class&nbsp;from&nbsp;the&nbsp;current&nbsp;namespace&nbsp;as&nbsp;a&nbsp;type&nbsp;hint<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">test</span><span style="color: #007700">(</span><span style="color: #0000BB">MyClass&nbsp;$typehintexample&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">null</span><span style="color: #007700">)&nbsp;{}<br /></span><span style="color: #FF8000">//&nbsp;another&nbsp;way&nbsp;to&nbsp;use&nbsp;a&nbsp;class&nbsp;from&nbsp;the&nbsp;current&nbsp;namespace&nbsp;as&nbsp;a&nbsp;type&nbsp;hint<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">test</span><span style="color: #007700">(\</span><span style="color: #0000BB">foo</span><span style="color: #007700">\</span><span style="color: #0000BB">MyClass&nbsp;$typehintexample&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">null</span><span style="color: #007700">)&nbsp;{}<br /><br /></span><span style="color: #FF8000">//&nbsp;extending&nbsp;a&nbsp;class&nbsp;from&nbsp;the&nbsp;current&nbsp;namespace<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">Extended&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">MyClass&nbsp;</span><span style="color: #007700">{}<br /><br /></span><span style="color: #FF8000">//&nbsp;accessing&nbsp;a&nbsp;global&nbsp;function<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;\</span><span style="color: #0000BB">globalfunc</span><span style="color: #007700">();<br /><br /></span><span style="color: #FF8000">//&nbsp;accessing&nbsp;a&nbsp;global&nbsp;constant<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;\</span><span style="color: #0000BB">INI_ALL</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div>
  <div class="sect2" id="language.namespaces.faq.full">
   <h3 class="title">
     How does a name like <em>\my\name</em> or <em>\name</em>
     resolve?
   </h3>
   <p class="para">
    Names that begin with a <em>\</em> always resolve to what they
    look like, so <em>\my\name</em> is in fact <em>my\name</em>,
    and <em>\Exception</em> is <em>Exception</em>.
    <div class="example" id="example-255">
     <p><strong>Example #5 Fully Qualified names</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;\</span><span style="color: #0000BB">my</span><span style="color: #007700">\</span><span style="color: #0000BB">name</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;"my\name"&nbsp;class<br /></span><span style="color: #007700">echo&nbsp;\</span><span style="color: #0000BB">strlen</span><span style="color: #007700">(</span><span style="color: #DD0000">'hi'</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;function&nbsp;"strlen"<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;\</span><span style="color: #0000BB">INI_ALL</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;$a&nbsp;is&nbsp;set&nbsp;to&nbsp;the&nbsp;value&nbsp;of&nbsp;constant&nbsp;"INI_ALL"<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div>
  <div class="sect2" id="language.namespaces.faq.qualified">
   <h3 class="title">How does a name like <em>my\name</em> resolve?</h3>
   <p class="para">
    Names that contain a backslash but do not begin with a backslash like
    <em>my\name</em> can be resolved in 2 different ways.
   </p>
   <p class="para">
    If there is
    an import statement that aliases another name to <em>my</em>, then
    the import alias is applied to the <em>my</em> in <em>my\name</em>.
   </p>
   <p class="para">
    Otherwise, the current namespace name is prepended to <em>my\name</em>.
   </p>
   <p class="para">
    <div class="example" id="example-256">
     <p><strong>Example #6 Qualified names</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">;<br />use&nbsp;</span><span style="color: #0000BB">blah</span><span style="color: #007700">\</span><span style="color: #0000BB">blah&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">my</span><span style="color: #007700">\</span><span style="color: #0000BB">name</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;"foo\my\name"&nbsp;class<br /></span><span style="color: #0000BB">foo</span><span style="color: #007700">\</span><span style="color: #0000BB">bar</span><span style="color: #007700">::</span><span style="color: #0000BB">name</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;static&nbsp;method&nbsp;"name"&nbsp;in&nbsp;class&nbsp;"blah\blah\bar"<br /></span><span style="color: #0000BB">my</span><span style="color: #007700">\</span><span style="color: #0000BB">bar</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;function&nbsp;"foo\my\bar"<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">my</span><span style="color: #007700">\</span><span style="color: #0000BB">BAR</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;sets&nbsp;$a&nbsp;to&nbsp;the&nbsp;value&nbsp;of&nbsp;constant&nbsp;"foo\my\BAR"<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div>
  <div class="sect2" id="language.namespaces.faq.shortname1">
   <h3 class="title">How does an unqualified class name like <em>name</em> resolve?</h3>
   <p class="para">
    Class names that do not contain a backslash like
    <em>name</em> can be resolved in 2 different ways.
   </p>
   <p class="para">
    If there is
    an import statement that aliases another name to <em>name</em>, then
    the import alias is applied.
   </p>
   <p class="para">
    Otherwise, the current namespace name is prepended to <em>name</em>.
   </p>
   <p class="para">
    <div class="example" id="example-257">
     <p><strong>Example #7 Unqualified class names</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">;<br />use&nbsp;</span><span style="color: #0000BB">blah</span><span style="color: #007700">\</span><span style="color: #0000BB">blah&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">name</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;"foo\name"&nbsp;class<br /></span><span style="color: #0000BB">foo</span><span style="color: #007700">::</span><span style="color: #0000BB">name</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;static&nbsp;method&nbsp;"name"&nbsp;in&nbsp;class&nbsp;"blah\blah"<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div>
  <div class="sect2" id="language.namespaces.faq.shortname2">
   <h3 class="title">
    How does an unqualified function name or unqualified constant name
    like <em>name</em> resolve?
   </h3>
   <p class="para">
    Function or constant names that do not contain a backslash like
    <em>name</em> can be resolved in 2 different ways.
   </p>
   <p class="para">
    First, the current namespace name is prepended to <em>name</em>.
   </p>
   <p class="para">
    Finally, if the constant or function <em>name</em> does not exist
    in the current namespace, a global constant or function <em>name</em>
    is used if it exists.
   </p>
   <p class="para">
    <div class="example" id="example-258">
     <p><strong>Example #8 Unqualified function or constant names</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">;<br />use&nbsp;</span><span style="color: #0000BB">blah</span><span style="color: #007700">\</span><span style="color: #0000BB">blah&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">;<br /><br />const&nbsp;</span><span style="color: #0000BB">FOO&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br /><br />function&nbsp;</span><span style="color: #0000BB">my</span><span style="color: #007700">()&nbsp;{}<br />function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">()&nbsp;{}<br />function&nbsp;</span><span style="color: #0000BB">sort</span><span style="color: #007700">(&amp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">)<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">sort</span><span style="color: #007700">(</span><span style="color: #0000BB">$a</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">array_flip</span><span style="color: #007700">(</span><span style="color: #0000BB">$a</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">my</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;"foo\my"<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">strlen</span><span style="color: #007700">(</span><span style="color: #DD0000">'hi'</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;global&nbsp;function&nbsp;"strlen"&nbsp;because&nbsp;"foo\strlen"&nbsp;does&nbsp;not&nbsp;exist<br /></span><span style="color: #0000BB">$arr&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #0000BB">1</span><span style="color: #007700">,</span><span style="color: #0000BB">3</span><span style="color: #007700">,</span><span style="color: #0000BB">2</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">sort</span><span style="color: #007700">(</span><span style="color: #0000BB">$arr</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;function&nbsp;"foo\sort"<br /></span><span style="color: #0000BB">$c&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;function&nbsp;"foo\foo"&nbsp;-&nbsp;import&nbsp;is&nbsp;not&nbsp;applied<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">FOO</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;sets&nbsp;$a&nbsp;to&nbsp;value&nbsp;of&nbsp;constant&nbsp;"foo\FOO"&nbsp;-&nbsp;import&nbsp;is&nbsp;not&nbsp;applied<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">INI_ALL</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;sets&nbsp;$b&nbsp;to&nbsp;value&nbsp;of&nbsp;global&nbsp;constant&nbsp;"INI_ALL"<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div>
  <div class="sect2" id="language.namespaces.faq.conflict">
   <h3 class="title">Import names cannot conflict with classes defined in the same file.</h3>
   <p class="para">
    The following script combinations are legal:
    <div class="informalexample">
     <p class="simpara">file1.php</p>
     <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">my</span><span style="color: #007700">\</span><span style="color: #0000BB">stuff</span><span style="color: #007700">;<br />class&nbsp;</span><span style="color: #0000BB">MyClass&nbsp;</span><span style="color: #007700">{}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

     <p class="simpara">another.php</p>
     <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">another</span><span style="color: #007700">;<br />class&nbsp;</span><span style="color: #0000BB">thing&nbsp;</span><span style="color: #007700">{}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

     <p class="simpara">file2.php</p>
     <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">my</span><span style="color: #007700">\</span><span style="color: #0000BB">stuff</span><span style="color: #007700">;<br />include&nbsp;</span><span style="color: #DD0000">'file1.php'</span><span style="color: #007700">;<br />include&nbsp;</span><span style="color: #DD0000">'another.php'</span><span style="color: #007700">;<br /><br />use&nbsp;</span><span style="color: #0000BB">another</span><span style="color: #007700">\</span><span style="color: #0000BB">thing&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;class&nbsp;"thing"&nbsp;from&nbsp;namespace&nbsp;another<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    There is no name conflict, even though the class <em>MyClass</em> exists
    within the <em>my\stuff</em> namespace, because the MyClass definition is
    in a separate file.  However, the next example causes a fatal error on name conflict
    because MyClass is defined in the same file as the use statement.
    <div class="informalexample">
     <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">my</span><span style="color: #007700">\</span><span style="color: #0000BB">stuff</span><span style="color: #007700">;<br />use&nbsp;</span><span style="color: #0000BB">another</span><span style="color: #007700">\</span><span style="color: #0000BB">thing&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">;<br />class&nbsp;</span><span style="color: #0000BB">MyClass&nbsp;</span><span style="color: #007700">{}&nbsp;</span><span style="color: #FF8000">//&nbsp;fatal&nbsp;error:&nbsp;MyClass&nbsp;conflicts&nbsp;with&nbsp;import&nbsp;statement<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div>
  <div class="sect2" id="language.namespaces.faq.nested">
   <h3 class="title">Nested namespaces are not allowed.</h3>
   <p class="para">
    PHP does not allow nesting namespaces
    <div class="informalexample">
     <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">my</span><span style="color: #007700">\</span><span style="color: #0000BB">stuff&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;namespace&nbsp;</span><span style="color: #0000BB">nested&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;class&nbsp;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">{}<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
    However, it is easy to simulate nested namespaces like so:
    <div class="informalexample">
     <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">my</span><span style="color: #007700">\</span><span style="color: #0000BB">stuff</span><span style="color: #007700">\</span><span style="color: #0000BB">nested&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;class&nbsp;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">{}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div>
  <div class="sect2" id="language.namespaces.faq.nofuncconstantuse">
   <h3 class="title">Neither functions nor constants can be imported via the <em>use</em>
      statement.</h3>
   <p class="para">
    The only elements that are affected by <em>use</em> statements are namespaces
    and class names.  In order to shorten a long constant or function, import its containing
    namespace
    <div class="informalexample">
     <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">mine</span><span style="color: #007700">;<br />use&nbsp;</span><span style="color: #0000BB">ultra</span><span style="color: #007700">\</span><span style="color: #0000BB">long</span><span style="color: #007700">\</span><span style="color: #0000BB">ns</span><span style="color: #007700">\</span><span style="color: #0000BB">name</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">name</span><span style="color: #007700">\</span><span style="color: #0000BB">CONSTANT</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">name</span><span style="color: #007700">\</span><span style="color: #0000BB">func</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div>
  <div class="sect2" id="language.namespaces.faq.quote">
   <h3 class="title">Dynamic namespace names (quoted identifiers) should escape backslash</h3>
   <p class="para">
    It is very important to realize that because the backslash is used as an escape character
    within strings, it should always be doubled when used inside a string.  Otherwise
    there is a risk of unintended consequences:
    <div class="example" id="example-259">
     <p><strong>Example #9 Dangers of using namespaced names inside a double-quoted string</strong></p>
     <div class="example-contents">
      <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #DD0000">"dangerous\name"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;\n&nbsp;is&nbsp;a&nbsp;newline&nbsp;inside&nbsp;double&nbsp;quoted&nbsp;strings!<br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #DD0000">'not\at\all\dangerous'</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;no&nbsp;problems&nbsp;here.<br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
    Inside a single-quoted string, the backslash escape sequence is much safer to use, but it
    is still recommended practice to escape backslashes in all strings as a best practice.
   </p>
  </div>
  <div class="sect2" id="language.namespaces.faq.constants">
   <h3 class="title">Undefined Constants referenced using any backslash die with fatal error</h3>
   <p class="para">
    Any undefined constant that is unqualified like <em>FOO</em> will
    produce a notice explaining that PHP assumed <em>FOO</em> was the value
    of the constant.  Any constant, qualified or fully qualified, that contains a
    backslash will produce a fatal error if not found.
    <div class="example" id="example-260">
     <p><strong>Example #10 Undefined constants</strong></p>
     <div class="example-contents">
      <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">bar</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">FOO</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;produces&nbsp;notice&nbsp;-&nbsp;undefined&nbsp;constants&nbsp;"FOO"&nbsp;assumed&nbsp;"FOO";<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;\</span><span style="color: #0000BB">FOO</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;fatal&nbsp;error,&nbsp;undefined&nbsp;namespace&nbsp;constant&nbsp;FOO<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">Bar</span><span style="color: #007700">\</span><span style="color: #0000BB">FOO</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;fatal&nbsp;error,&nbsp;undefined&nbsp;namespace&nbsp;constant&nbsp;bar\Bar\FOO<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;\</span><span style="color: #0000BB">Bar</span><span style="color: #007700">\</span><span style="color: #0000BB">FOO</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;fatal&nbsp;error,&nbsp;undefined&nbsp;namespace&nbsp;constant&nbsp;Bar\FOO<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div>
  <div class="sect2" id="language.namespaces.faq.builtinconst">
   <h3 class="title">Cannot override special constants NULL, TRUE, FALSE, ZEND_THREAD_SAFE or ZEND_DEBUG_BUILD</h3>
   <p class="para">
    Any attempt to define a namespaced constant that is a special, built-in constant
    results in a fatal error
    <div class="example" id="example-261">
     <p><strong>Example #11 Undefined constants</strong></p>
     <div class="example-contents">
      <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">bar</span><span style="color: #007700">;<br />const&nbsp;</span><span style="color: #0000BB">NULL&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;fatal&nbsp;error;<br /></span><span style="color: #007700">const&nbsp;</span><span style="color: #0000BB">true&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'stupid'</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;also&nbsp;fatal&nbsp;error;<br />//&nbsp;etc.<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div>
 </div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.namespaces.faq&amp;redirect=http://www.php.net/manual/en/language.namespaces.faq.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.faq&amp;redirect=http://www.php.net/manual/en/language.namespaces.faq.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>FAQ: things you need to know about namespaces</strong>
 </div><div id="allnotes">
 <a name="108236"></a>
 <div class="note">
  <strong class='user'>manolachef at gmail dot com</strong>
  <a href="#108236" class="date">10-Apr-2012 02:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
There is a way to define a namespaced constant that is a special, built-in constant, using define function and setting the third parameter case_insensitive to false:<br />
<br />
<span class="default">&lt;?php<br />
namespace foo</span><span class="keyword">;<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="default">__NAMESPACE__ </span><span class="keyword">. </span><span class="string">'\NULL'</span><span class="keyword">, </span><span class="default">10</span><span class="keyword">); </span><span class="comment">// defines the constant NULL in the current namespace<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">NULL</span><span class="keyword">); </span><span class="comment">// will show 10<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">null</span><span class="keyword">); </span><span class="comment">// will show NULL<br />
</span><span class="default">?&gt;<br />
</span><br />
&nbsp; No need to specify the namespace in your call to define(), like it happens usually<br />
<span class="default">&lt;?php<br />
namespace foo</span><span class="keyword">;<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="default">INI_ALL</span><span class="keyword">, </span><span class="string">'bar'</span><span class="keyword">); </span><span class="comment">// produces notice - Constant INI_ALL already defined. But:<br />
<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="default">__NAMESPACE__ </span><span class="keyword">. </span><span class="string">'\INI_ALL'</span><span class="keyword">, </span><span class="string">'bar'</span><span class="keyword">); </span><span class="comment">// defines the constant INI_ALL in the current namespace<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">INI_ALL</span><span class="keyword">); </span><span class="comment">// will show string(3)"bar". Nothing unespected so far. But:<br />
<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'NULL'</span><span class="keyword">, </span><span class="default">10</span><span class="keyword">); </span><span class="comment">// defines the constant NULL in the current namespace...<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">NULL</span><span class="keyword">); </span><span class="comment">// will show 10<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">null</span><span class="keyword">); </span><span class="comment">// will show NULL<br />
</span><span class="default">?&gt;<br />
</span><br />
&nbsp; If the parameter case_insensitive is set to true<br />
<span class="default">&lt;?php<br />
namespace foo</span><span class="keyword">;<br />
</span><span class="default">define </span><span class="keyword">(</span><span class="default">__NAMESPACE__ </span><span class="keyword">. </span><span class="string">'\NULL'</span><span class="keyword">, </span><span class="default">10</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">); </span><span class="comment">// produces notice - Constant null already defined<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.namespaces.faq&amp;redirect=http://www.php.net/manual/en/language.namespaces.faq.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.faq&amp;redirect=http://www.php.net/manual/en/language.namespaces.faq.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.namespaces.faq.php">show source</a> |
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