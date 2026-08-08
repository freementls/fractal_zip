<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Language Reference - Manual</title>
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
 <link rel="index" href="index.php" />
 <link rel="prev" href="configuration.changes.php" />
 <link rel="next" href="language.basic-syntax.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/langref" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/langref.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/langref.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/langref.php" />
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
 <li><a href="copyright.php">Copyright</a></li>
 <li><a href="manual.php">PHP Manual</a></li>
 <li><a href="getting-started.php">Getting Started</a></li>
 <li><a href="install.php">Installation and Configuration</a></li>
 <li class="active"><a href="langref.php">Language Reference</a></li>
 <li><a href="security.php">Security</a></li>
 <li><a href="features.php">Features</a></li>
 <li><a href="funcref.php">Function Reference</a></li>
 <li><a href="internals2.php">PHP at the Core: A Hacker's Guide to the Zend Engine</a></li>
 <li><a href="faq.php">FAQ</a></li>
 <li><a href="appendices.php">Appendices</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.basic-syntax.php">Basic syntax<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="configuration.changes.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />How to change configuration settings</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/langref.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/langref.php">Brazilian Portuguese</option>
    <option value="zh/langref.php">Chinese (Simplified)</option>
    <option value="fr/langref.php">French</option>
    <option value="de/langref.php">German</option>
    <option value="ja/langref.php">Japanese</option>
    <option value="pl/langref.php">Polish</option>
    <option value="ro/langref.php">Romanian</option>
    <option value="ru/langref.php">Russian</option>
    <option value="fa/langref.php">Persian</option>
    <option value="es/langref.php">Spanish</option>
    <option value="tr/langref.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="langref" class="book">
  <h1 class="title">Language Reference</h1>
  

 



  


 


  

 
 


  

 
 


  

 
 


  

 



  





  

 
 


  

 
 


  





  

 
 


  

 
 


  



 

 

  







  







  



 



  






 <ul class="chunklist chunklist_book"><li><a href="language.basic-syntax.php">Basic syntax</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="language.basic-syntax.phptags.php">PHP tags</a></li><li><a href="language.basic-syntax.phpmode.php">Escaping from HTML</a></li><li><a href="language.basic-syntax.instruction-separation.php">Instruction separation</a></li><li><a href="language.basic-syntax.comments.php">Comments</a></li></ul></li><li><a href="language.types.php">Types</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="language.types.intro.php">Introduction</a></li><li><a href="language.types.boolean.php">Booleans</a></li><li><a href="language.types.integer.php">Integers</a></li><li><a href="language.types.float.php">Floating point numbers</a></li><li><a href="language.types.string.php">Strings</a></li><li><a href="language.types.array.php">Arrays</a></li><li><a href="language.types.object.php">Objects</a></li><li><a href="language.types.resource.php">Resources</a></li><li><a href="language.types.null.php">NULL</a></li><li><a href="language.types.callable.php">Callbacks</a></li><li><a href="language.pseudo-types.php">Pseudo-types and variables used in this documentation</a></li><li><a href="language.types.type-juggling.php">Type Juggling</a></li></ul></li><li><a href="language.variables.php">Variables</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="language.variables.basics.php">Basics</a></li><li><a href="language.variables.predefined.php">Predefined Variables</a></li><li><a href="language.variables.scope.php">Variable scope</a></li><li><a href="language.variables.variable.php">Variable variables</a></li><li><a href="language.variables.external.php">Variables From External Sources</a></li></ul></li><li><a href="language.constants.php">Constants</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="language.constants.syntax.php">Syntax</a></li><li><a href="language.constants.predefined.php">Magic constants</a></li></ul></li><li><a href="language.expressions.php">Expressions</a></li><li><a href="language.operators.php">Operators</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="language.operators.precedence.php">Operator Precedence</a></li><li><a href="language.operators.arithmetic.php">Arithmetic Operators</a></li><li><a href="language.operators.assignment.php">Assignment Operators</a></li><li><a href="language.operators.bitwise.php">Bitwise Operators</a></li><li><a href="language.operators.comparison.php">Comparison Operators</a></li><li><a href="language.operators.errorcontrol.php">Error Control Operators</a></li><li><a href="language.operators.execution.php">Execution Operators</a></li><li><a href="language.operators.increment.php">Incrementing/Decrementing Operators</a></li><li><a href="language.operators.logical.php">Logical Operators</a></li><li><a href="language.operators.string.php">String Operators</a></li><li><a href="language.operators.array.php">Array Operators</a></li><li><a href="language.operators.type.php">Type Operators</a></li></ul></li><li><a href="language.control-structures.php">Control Structures</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="control-structures.intro.php">Introduction</a></li><li><a href="control-structures.if.php">if</a></li><li><a href="control-structures.else.php">else</a></li><li><a href="control-structures.elseif.php">elseif/else if</a></li><li><a href="control-structures.alternative-syntax.php">Alternative syntax for control structures</a></li><li><a href="control-structures.while.php">while</a></li><li><a href="control-structures.do.while.php">do-while</a></li><li><a href="control-structures.for.php">for</a></li><li><a href="control-structures.foreach.php">foreach</a></li><li><a href="control-structures.break.php">break</a></li><li><a href="control-structures.continue.php">continue</a></li><li><a href="control-structures.switch.php">switch</a></li><li><a href="control-structures.declare.php">declare</a></li><li><a href="function.return.php">return</a></li><li><a href="function.require.php">require</a></li><li><a href="function.include.php">include</a></li><li><a href="function.require-once.php">require_once</a></li><li><a href="function.include-once.php">include_once</a></li><li><a href="control-structures.goto.php">goto</a></li></ul></li><li><a href="language.functions.php">Functions</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="functions.user-defined.php">User-defined functions</a></li><li><a href="functions.arguments.php">Function arguments</a></li><li><a href="functions.returning-values.php">Returning values</a></li><li><a href="functions.variable-functions.php">Variable functions</a></li><li><a href="functions.internal.php">Internal (built-in) functions</a></li><li><a href="functions.anonymous.php">Anonymous functions</a></li></ul></li><li><a href="language.oop5.php">Classes and Objects</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="oop5.intro.php">Introduction</a></li><li><a href="language.oop5.basic.php">The Basics</a></li><li><a href="language.oop5.properties.php">Properties</a></li><li><a href="language.oop5.constants.php">Class Constants</a></li><li><a href="language.oop5.autoload.php">Autoloading Classes</a></li><li><a href="language.oop5.decon.php">Constructors and Destructors</a></li><li><a href="language.oop5.visibility.php">Visibility</a></li><li><a href="language.oop5.inheritance.php">Object Inheritance</a></li><li><a href="language.oop5.paamayim-nekudotayim.php">Scope Resolution Operator (::)</a></li><li><a href="language.oop5.static.php">Static Keyword</a></li><li><a href="language.oop5.abstract.php">Class Abstraction</a></li><li><a href="language.oop5.interfaces.php">Object Interfaces</a></li><li><a href="language.oop5.traits.php">Traits</a></li><li><a href="language.oop5.overloading.php">Overloading</a></li><li><a href="language.oop5.iterations.php">Object Iteration</a></li><li><a href="language.oop5.magic.php">Magic Methods</a></li><li><a href="language.oop5.final.php">Final Keyword</a></li><li><a href="language.oop5.cloning.php">Object Cloning</a></li><li><a href="language.oop5.object-comparison.php">Comparing Objects</a></li><li><a href="language.oop5.typehinting.php">Type Hinting</a></li><li><a href="language.oop5.late-static-bindings.php">Late Static Bindings</a></li><li><a href="language.oop5.references.php">Objects and references</a></li><li><a href="language.oop5.serialization.php">Object Serialization</a></li><li><a href="language.oop5.changelog.php">OOP Changelog</a></li></ul></li><li><a href="language.namespaces.php">Namespaces</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="language.namespaces.rationale.php">Namespaces overview</a></li><li><a href="language.namespaces.definition.php">Defining namespaces</a></li><li><a href="language.namespaces.nested.php">Declaring sub-namespaces</a></li><li><a href="language.namespaces.definitionmultiple.php">Defining multiple namespaces in the same file</a></li><li><a href="language.namespaces.basics.php">Using namespaces: Basics</a></li><li><a href="language.namespaces.dynamic.php">Namespaces and dynamic language features</a></li><li><a href="language.namespaces.nsconstants.php">namespace keyword and __NAMESPACE__ constant</a></li><li><a href="language.namespaces.importing.php">Using namespaces: Aliasing/Importing</a></li><li><a href="language.namespaces.global.php">Global space</a></li><li><a href="language.namespaces.fallback.php">Using namespaces: fallback to global function/constant</a></li><li><a href="language.namespaces.rules.php">Name resolution rules</a></li><li><a href="language.namespaces.faq.php">FAQ: things you need to know about namespaces</a></li></ul></li><li><a href="language.exceptions.php">Exceptions</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="language.exceptions.extending.php">Extending Exceptions</a></li></ul></li><li><a href="language.references.php">References Explained</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="language.references.whatare.php">What References Are</a></li><li><a href="language.references.whatdo.php">What References Do</a></li><li><a href="language.references.arent.php">What References Are Not</a></li><li><a href="language.references.pass.php">Passing by Reference</a></li><li><a href="language.references.return.php">Returning References</a></li><li><a href="language.references.unset.php">Unsetting References</a></li><li><a href="language.references.spot.php">Spotting References</a></li></ul></li><li><a href="reserved.variables.php">Predefined Variables</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="language.variables.superglobals.php">Superglobals</a> — Superglobals are built-in variables that are always available in all scopes</li><li><a href="reserved.variables.globals.php">$GLOBALS</a> — References all variables available in global scope</li><li><a href="reserved.variables.server.php">$_SERVER</a> — Server and execution environment information</li><li><a href="reserved.variables.get.php">$_GET</a> — HTTP GET variables</li><li><a href="reserved.variables.post.php">$_POST</a> — HTTP POST variables</li><li><a href="reserved.variables.files.php">$_FILES</a> — HTTP File Upload variables</li><li><a href="reserved.variables.request.php">$_REQUEST</a> — HTTP Request variables</li><li><a href="reserved.variables.session.php">$_SESSION</a> — Session variables</li><li><a href="reserved.variables.environment.php">$_ENV</a> — Environment variables</li><li><a href="reserved.variables.cookies.php">$_COOKIE</a> — HTTP Cookies</li><li><a href="reserved.variables.phperrormsg.php">$php_errormsg</a> — The previous error message</li><li><a href="reserved.variables.httprawpostdata.php">$HTTP_RAW_POST_DATA</a> — Raw POST data</li><li><a href="reserved.variables.httpresponseheader.php">$http_response_header</a> — HTTP response headers</li><li><a href="reserved.variables.argc.php">$argc</a> — The number of arguments passed to script</li><li><a href="reserved.variables.argv.php">$argv</a> — Array of arguments passed to script</li></ul></li><li><a href="reserved.exceptions.php">Predefined Exceptions</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="class.exception.php">Exception</a></li><li><a href="class.errorexception.php">ErrorException</a></li></ul></li><li><a href="reserved.interfaces.php">Predefined Interfaces</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="class.traversable.php">Traversable</a> — The Traversable interface</li><li><a href="class.iterator.php">Iterator</a> — The Iterator interface</li><li><a href="class.iteratoraggregate.php">IteratorAggregate</a> — The IteratorAggregate interface</li><li><a href="class.arrayaccess.php">ArrayAccess</a> — The ArrayAccess interface</li><li><a href="class.serializable.php">Serializable</a> — The Serializable interface</li><li><a href="class.closure.php">Closure</a> — The Closure class</li></ul></li><li><a href="context.php">Context options and parameters</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="context.socket.php">Socket context options</a> — Socket context option listing</li><li><a href="context.http.php">HTTP context options</a> — HTTP context option listing</li><li><a href="context.ftp.php">FTP context options</a> — FTP context option listing</li><li><a href="context.ssl.php">SSL context options</a> — SSL context option listing</li><li><a href="context.curl.php">CURL context options</a> — CURL context option listing</li><li><a href="context.phar.php">Phar context options</a> — Phar context option listing</li><li><a href="context.params.php">Context parameters</a> — Context parameter listing</li></ul></li><li><a href="wrappers.php">Supported Protocols and Wrappers</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="wrappers.file.php">file://</a> — Accessing local filesystem</li><li><a href="wrappers.http.php">http://</a> — Accessing HTTP(s) URLs</li><li><a href="wrappers.ftp.php">ftp://</a> — Accessing FTP(s) URLs</li><li><a href="wrappers.php.php">php://</a> — Accessing various I/O streams</li><li><a href="wrappers.compression.php">zlib://</a> — Compression Streams</li><li><a href="wrappers.data.php">data://</a> — Data (RFC 2397)</li><li><a href="wrappers.glob.php">glob://</a> — Find pathnames matching pattern</li><li><a href="wrappers.phar.php">phar://</a> — PHP Archive</li><li><a href="wrappers.ssh2.php">ssh2://</a> — Secure Shell 2</li><li><a href="wrappers.rar.php">rar://</a> — RAR</li><li><a href="wrappers.audio.php">ogg://</a> — Audio streams</li><li><a href="wrappers.expect.php">expect://</a> — Process Interaction Streams</li></ul></li></ul></div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=langref&amp;redirect=http://www.php.net/manual/en/langref.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=langref&amp;redirect=http://www.php.net/manual/en/langref.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Language Reference</strong>
 </div>
 <div class="note">There are no user contributed notes for this page.</div></div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/langref.php">show source</a> |
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