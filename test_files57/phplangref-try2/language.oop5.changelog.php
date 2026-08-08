<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: OOP Changelog - Manual</title>
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
 <link rel="index" href="language.oop5.php" />
 <link rel="prev" href="language.oop5.serialization.php" />
 <link rel="next" href="language.namespaces.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.changelog" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.changelog.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.oop5.changelog.php" />
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
 <li class="header up"><a href="language.oop5.php">Classes and Objects</a></li>
 <li><a href="oop5.intro.php">Introduction</a></li>
 <li><a href="language.oop5.basic.php">The Basics</a></li>
 <li><a href="language.oop5.properties.php">Properties</a></li>
 <li><a href="language.oop5.constants.php">Class Constants</a></li>
 <li><a href="language.oop5.autoload.php">Autoloading Classes</a></li>
 <li><a href="language.oop5.decon.php">Constructors and Destructors</a></li>
 <li><a href="language.oop5.visibility.php">Visibility</a></li>
 <li><a href="language.oop5.inheritance.php">Object Inheritance</a></li>
 <li><a href="language.oop5.paamayim-nekudotayim.php">Scope Resolution Operator (::)</a></li>
 <li><a href="language.oop5.static.php">Static Keyword</a></li>
 <li><a href="language.oop5.abstract.php">Class Abstraction</a></li>
 <li><a href="language.oop5.interfaces.php">Object Interfaces</a></li>
 <li><a href="language.oop5.traits.php">Traits</a></li>
 <li><a href="language.oop5.overloading.php">Overloading</a></li>
 <li><a href="language.oop5.iterations.php">Object Iteration</a></li>
 <li><a href="language.oop5.magic.php">Magic Methods</a></li>
 <li><a href="language.oop5.final.php">Final Keyword</a></li>
 <li><a href="language.oop5.cloning.php">Object Cloning</a></li>
 <li><a href="language.oop5.object-comparison.php">Comparing Objects</a></li>
 <li><a href="language.oop5.typehinting.php">Type Hinting</a></li>
 <li><a href="language.oop5.late-static-bindings.php">Late Static Bindings</a></li>
 <li><a href="language.oop5.references.php">Objects and references</a></li>
 <li><a href="language.oop5.serialization.php">Object Serialization</a></li>
 <li class="active"><a href="language.oop5.changelog.php">OOP Changelog</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.namespaces.php">Namespaces<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.serialization.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Object Serialization</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.changelog.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.changelog.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.changelog.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.changelog.php">French</option>
    <option value="de/language.oop5.changelog.php">German</option>
    <option value="ja/language.oop5.changelog.php">Japanese</option>
    <option value="pl/language.oop5.changelog.php">Polish</option>
    <option value="ro/language.oop5.changelog.php">Romanian</option>
    <option value="ru/language.oop5.changelog.php">Russian</option>
    <option value="fa/language.oop5.changelog.php">Persian</option>
    <option value="es/language.oop5.changelog.php">Spanish</option>
    <option value="tr/language.oop5.changelog.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.changelog" class="sect1">
 <h2 class="title">OOP Changelog</h2>
 <p class="para">
  Changes to the PHP 5 OOP model are logged here. Descriptions and other notes regarding
  these features are documented within the OOP 5 documentation.
 </p>
 <p class="para">
  <table class="doctable informaltable">
   
    <thead>
     <tr>
      <th>Version</th>
      <th>Description</th>
     </tr>

    </thead>

    <tbody class="tbody">
     <tr>
      <td>5.4.0</td>
      <td>
       Changed: If an <a href="language.oop5.abstract.php" class="link">abstract</a> class
       defines a signature for the constructor it will now be enforced.
      </td>
     </tr>

     <tr>
      <td>5.3.3</td>
      <td>
       Changed: Methods with the same name as the last element of
       a <a href="language.namespaces.php" class="link">namespaced</a>
       class name will no longer be treated as <a href="language.oop5.decon.php" class="link">constructor</a>. This change doesn&#039;t
       affect non-namespaced classes.
      </td>
     </tr>

     <tr>
      <td>5.3.0</td>
      <td>
       Changed: Classes that implement interfaces with methods that have default 
       values in the prototype are no longer required to match the interface&#039;s default 
       value.
      </td>
     </tr>

     <tr>
      <td>5.3.0</td>
      <td>
       Changed: It&#039;s now possible to reference the class using a variable (e.g.,
       <em>echo $classname::constant;</em>).
       The variable&#039;s value can not be a keyword (e.g., <em>self</em>,
       <em>parent</em> or <em>static</em>).
      </td>
     </tr>

     <tr>
      <td>5.3.0</td>
      <td>
       Changed: An <strong><code>E_WARNING</code></strong> level error is issued if
       the magic <a href="language.oop5.overloading.php" class="link">overloading</a>
       methods are declared <a href="language.oop5.static.php" class="link">static</a>.
       It also enforces the public visibility requirement.
      </td>
     </tr>

     <tr>
      <td>5.3.0</td>
      <td>
       Changed: Prior to 5.3.0, exceptions thrown in the
        <span class="function"><a href="function.autoload.php" class="function">__autoload()</a></span> function could not be
       caught in the <a href="language.exceptions.php" class="link">catch</a> block, and
       would result in a fatal error. Exceptions now thrown in the __autoload function
       can be caught in the <a href="language.exceptions.php" class="link">catch</a> block, with
       one proviso. If throwing a custom exception, then the custom exception class must
       be available. The __autoload function may be used recursively to autoload the
       custom exception class.
      </td>
     </tr>

     <tr>
      <td>5.3.0</td>
      <td>
       Added: The <a href="language.oop5.overloading.php" class="link">__callStatic</a> method.
      </td>
     </tr>

     <tr>
      <td>5.3.0</td>
      <td>
       Added: <a href="language.types.string.php#language.types.string.syntax.heredoc" class="link">heredoc</a>
       and <a href="language.types.string.php#language.types.string.syntax.heredoc" class="link">nowdoc</a>
       support for class <em class="emphasis">const</em> and property definitions.
       Note: heredoc values must follow the same rules as double-quoted strings,
       (e.g., no variables within).
      </td>
     </tr>

     <tr>
      <td>5.3.0</td>
      <td>
       Added: <a href="language.oop5.late-static-bindings.php" class="link">Late Static Bindings</a>.
      </td>
     </tr>

     <tr>
      <td>5.3.0</td>
      <td>
       Added: The <a href="language.oop5.magic.php#object.invoke" class="link">__invoke()</a> method.
      </td>
     </tr>

     <tr>
      <td>5.2.0</td>
      <td>
       Changed: The <a href="language.oop5.magic.php#object.tostring" class="link">__toString()</a>
       method was only called when it was directly combined with
        <span class="function"><a href="function.echo.php" class="function">echo</a></span> or  <span class="function"><a href="function.print.php" class="function">print</a></span>.
       But now, it is called in any string context (e.g. in
        <span class="function"><a href="function.printf.php" class="function">printf()</a></span> with <em>%s</em> modifier) but not
       in other types contexts (e.g. with <em>%d</em> modifier).
       Since PHP 5.2.0, converting objects without a <em>__toString</em>
       method to string emits a <strong><code>E_RECOVERABLE_ERROR</code></strong> level error.
      </td>
     </tr>

     <tr>
      <td>5.1.3</td>
      <td>
       Changed: In previous versions of PHP 5, the use of <em>var</em>
       was considered deprecated and would issue an <strong><code>E_STRICT</code></strong>
       level error. It&#039;s no longer deprecated, therefore does not emit the error.
      </td>
     </tr>

     <tr>
      <td>5.1.0</td>
      <td>
       Changed: The <a href="language.oop5.magic.php#object.set-state" class="link">__set_state()</a> static
       method is now called for classes exported by  <span class="function"><a href="function.var-export.php" class="function">var_export()</a></span>.
      </td>
     </tr>

     <tr>
      <td>5.1.0</td>
      <td>
       Added: The <a href="language.oop5.overloading.php#object.isset" class="link">__isset()</a>
       and <a href="language.oop5.overloading.php#object.unset" class="link">__unset()</a> methods.
      </td>
     </tr>

    </tbody>
   
  </table>

 </p>
</div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.changelog&amp;redirect=http://www.php.net/manual/en/language.oop5.changelog.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.changelog&amp;redirect=http://www.php.net/manual/en/language.oop5.changelog.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>OOP Changelog</strong>
 </div>
 <div class="note">There are no user contributed notes for this page.</div></div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.changelog.php">show source</a> |
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