<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Basics - Manual</title>
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
 <link rel="index" href="language.variables.php" />
 <link rel="prev" href="language.variables.php" />
 <link rel="next" href="language.variables.predefined.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/variables.basics" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.variables.basics.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.variables.basics.php" />
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
 <li class="header up"><a href="language.variables.php">Variables</a></li>
 <li class="active"><a href="language.variables.basics.php">Basics</a></li>
 <li><a href="language.variables.predefined.php">Predefined Variables</a></li>
 <li><a href="language.variables.scope.php">Variable scope</a></li>
 <li><a href="language.variables.variable.php">Variable variables</a></li>
 <li><a href="language.variables.external.php">Variables From External Sources</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.variables.predefined.php">Predefined Variables<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.variables.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Variables</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.variables.basics.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.variables.basics.php">Brazilian Portuguese</option>
    <option value="zh/language.variables.basics.php">Chinese (Simplified)</option>
    <option value="fr/language.variables.basics.php">French</option>
    <option value="de/language.variables.basics.php">German</option>
    <option value="ja/language.variables.basics.php">Japanese</option>
    <option value="pl/language.variables.basics.php">Polish</option>
    <option value="ro/language.variables.basics.php">Romanian</option>
    <option value="ru/language.variables.basics.php">Russian</option>
    <option value="fa/language.variables.basics.php">Persian</option>
    <option value="es/language.variables.basics.php">Spanish</option>
    <option value="tr/language.variables.basics.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.variables.basics" class="sect1">
   <h2 class="title">Basics</h2>

   <p class="simpara">
    Variables in PHP are represented by a dollar sign followed by the
    name of the variable. The variable name is case-sensitive.
   </p>

   <p class="para">
    Variable names follow the same rules as other labels in PHP. A
    valid variable name starts with a letter or underscore, followed
    by any number of letters, numbers, or underscores. As a regular
    expression, it would be expressed thus:
    &#039;<em>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*</em>&#039;
   </p>
   
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <span class="simpara">
     For our purposes here, a letter is a-z, A-Z, and the bytes
     from 127 through 255 (<em>0x7f-0xff</em>).
    </span>
   </p></blockquote>

   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <span class="simpara">
     <em>$this</em> is a special variable that can&#039;t be
     assigned.
    </span>
   </p></blockquote>

   <div class="tip"><strong class="tip">Tip</strong><p class="simpara">See also the
<a href="userlandnaming.php" class="xref">Userland Naming Guide</a>.</p></div>

   <p class="para">
    For information on variable related functions, see the
    <a href="ref.var.php" class="link">Variable Functions Reference</a>.
   </p>

   <p class="para">
    <div class="informalexample">
     <div class="example-contents"> 
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$var&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Bob'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$Var&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Joe'</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$var</span><span style="color: #DD0000">,&nbsp;</span><span style="color: #0000BB">$Var</span><span style="color: #DD0000">"</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;outputs&nbsp;"Bob,&nbsp;Joe"<br /><br /></span><span style="color: #007700">$</span><span style="color: #0000BB">4site&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'not&nbsp;yet'</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;invalid;&nbsp;starts&nbsp;with&nbsp;a&nbsp;number<br /></span><span style="color: #0000BB">$_4site&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'not&nbsp;yet'</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;valid;&nbsp;starts&nbsp;with&nbsp;an&nbsp;underscore<br /></span><span style="color: #0000BB">$täyte&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'mansikka'</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;valid;&nbsp;'ä'&nbsp;is&nbsp;(Extended)&nbsp;ASCII&nbsp;228.<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>

   <p class="para">
    By default, variables are always assigned by value. That is to say,
    when you assign an expression to a variable, the entire value of
    the original expression is copied into the destination
    variable. This means, for instance, that after assigning one
    variable&#039;s value to another, changing one of those variables will
    have no effect on the other. For more information on this kind of
    assignment, see the chapter on <a href="language.expressions.php" class="link">Expressions</a>.
   </p>
   <p class="para">
    PHP also offers another way to assign values to variables:
    <a href="language.references.php" class="link">assign by reference</a>. 
    This means that the new variable simply references (in other words, 
    &quot;becomes an alias for&quot; or &quot;points to&quot;) the original variable. 
    Changes to the new variable affect the original, and vice versa. 
   </p>
   <p class="para">
    To assign by reference, simply prepend an ampersand (&amp;) to the
    beginning of the variable which is being assigned (the source
    variable). For instance, the following code snippet outputs &#039;<em>My
    name is Bob</em>&#039; twice:

    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Bob'</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Assign&nbsp;the&nbsp;value&nbsp;'Bob'&nbsp;to&nbsp;$foo<br /></span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;&amp;</span><span style="color: #0000BB">$foo</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Reference&nbsp;$foo&nbsp;via&nbsp;$bar.<br /></span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"My&nbsp;name&nbsp;is&nbsp;</span><span style="color: #0000BB">$bar</span><span style="color: #DD0000">"</span><span style="color: #007700">;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Alter&nbsp;$bar...<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">$bar</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #0000BB">$foo</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;$foo&nbsp;is&nbsp;altered&nbsp;too.<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>

   <p class="para">
    One important thing to note is that only named variables may be
    assigned by reference.
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">25</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;&amp;</span><span style="color: #0000BB">$foo</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;This&nbsp;is&nbsp;a&nbsp;valid&nbsp;assignment.<br /></span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;&amp;(</span><span style="color: #0000BB">24&nbsp;</span><span style="color: #007700">*&nbsp;</span><span style="color: #0000BB">7</span><span style="color: #007700">);&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Invalid;&nbsp;references&nbsp;an&nbsp;unnamed&nbsp;expression.<br /><br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">test</span><span style="color: #007700">()<br />{<br />&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">25</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;&amp;</span><span style="color: #0000BB">test</span><span style="color: #007700">();&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Invalid.<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   
   <p class="para">
    It is not necessary to initialize variables in PHP however it is a very
    good practice. Uninitialized variables have a default value of their type depending on the context in which they are used
    - booleans default to <strong><code>FALSE</code></strong>, integers and floats default to zero, strings (e.g. used in  <span class="function"><a href="function.echo.php" class="function">echo</a></span>) are 
    set as an empty string and arrays become to an empty array.
   </p>
   <p class="para">
    <div class="example" id="example-97">
     <p><strong>Example #1 Default values of uninitialized variables</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">//&nbsp;Unset&nbsp;AND&nbsp;unreferenced&nbsp;(no&nbsp;use&nbsp;context)&nbsp;variable;&nbsp;outputs&nbsp;NULL<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$unset_var</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;Boolean&nbsp;usage;&nbsp;outputs&nbsp;'false'&nbsp;(See&nbsp;ternary&nbsp;operators&nbsp;for&nbsp;more&nbsp;on&nbsp;this&nbsp;syntax)<br /></span><span style="color: #007700">echo(</span><span style="color: #0000BB">$unset_bool&nbsp;</span><span style="color: #007700">?&nbsp;</span><span style="color: #DD0000">"true\n"&nbsp;</span><span style="color: #007700">:&nbsp;</span><span style="color: #DD0000">"false\n"</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;String&nbsp;usage;&nbsp;outputs&nbsp;'string(3)&nbsp;"abc"'<br /></span><span style="color: #0000BB">$unset_str&nbsp;</span><span style="color: #007700">.=&nbsp;</span><span style="color: #DD0000">'abc'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$unset_str</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;Integer&nbsp;usage;&nbsp;outputs&nbsp;'int(25)'<br /></span><span style="color: #0000BB">$unset_int&nbsp;</span><span style="color: #007700">+=&nbsp;</span><span style="color: #0000BB">25</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;0&nbsp;+&nbsp;25&nbsp;=&gt;&nbsp;25<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$unset_int</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;Float/double&nbsp;usage;&nbsp;outputs&nbsp;'float(1.25)'<br /></span><span style="color: #0000BB">$unset_float&nbsp;</span><span style="color: #007700">+=&nbsp;</span><span style="color: #0000BB">1.25</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$unset_float</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;Array&nbsp;usage;&nbsp;outputs&nbsp;array(1)&nbsp;{&nbsp;&nbsp;[3]=&gt;&nbsp;&nbsp;string(3)&nbsp;"def"&nbsp;}<br /></span><span style="color: #0000BB">$unset_arr</span><span style="color: #007700">[</span><span style="color: #0000BB">3</span><span style="color: #007700">]&nbsp;=&nbsp;</span><span style="color: #DD0000">"def"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;array()&nbsp;+&nbsp;array(3&nbsp;=&gt;&nbsp;"def")&nbsp;=&gt;&nbsp;array(3&nbsp;=&gt;&nbsp;"def")<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$unset_arr</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;Object&nbsp;usage;&nbsp;creates&nbsp;new&nbsp;stdClass&nbsp;object&nbsp;(see&nbsp;http://www.php.net/manual/en/reserved.classes.php)<br />//&nbsp;Outputs:&nbsp;object(stdClass)#1&nbsp;(1)&nbsp;{&nbsp;&nbsp;["foo"]=&gt;&nbsp;&nbsp;string(3)&nbsp;"bar"&nbsp;}<br /></span><span style="color: #0000BB">$unset_obj</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'bar'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$unset_obj</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    Relying on the default value of an uninitialized variable is problematic
    in the case of including one file into another which uses the same
    variable name. It is also a major <a href="security.globals.php" class="link">security risk</a> with <a href="ini.core.php#ini.register-globals" class="link">register_globals</a> turned on. <a href="" class="link">E_NOTICE</a> level error is issued in case of
    working with uninitialized variables, however not in the case of appending
    elements to the uninitialized array.  <span class="function"><a href="function.isset.php" class="function">isset()</a></span> language
    construct can be used to detect if a variable has been already initialized.
   </p>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.variables.predefined.php">Predefined Variables<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.variables.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Variables</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.variables.basics.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.variables.basics&amp;redirect=http://www.php.net/manual/en/language.variables.basics.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.variables.basics&amp;redirect=http://www.php.net/manual/en/language.variables.basics.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Basics</strong>
 </div><div id="allnotes">
 <a name="109109"></a>
 <div class="note">
  <strong class='user'>donatj at gmail dot com</strong>
  <a href="#109109" class="date">20-Jun-2012 10:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The regular expression they provide is not correct.&nbsp; It *is* a good recommendation but having inherited several cyrillic projects over the years, I know this not to be the case. Many many UTF-8 characters outside that range can be used as variables. <br />
<br />
As an example, try:<br />
<br />
<span class="default">&lt;?php<br />
$Ελληνικά </span><span class="keyword">= </span><span class="string">"Γειά σου κόσμος (Hello World)!"</span><span class="keyword">;<br />
echo </span><span class="default">$Ελληνικά</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
It does in fact return the promised result!<br />
<br />
Γειά σου κόσμος (Hello World)!<br />
<br />
And all of those letters, including the E appearing thing are outside of the extended ASCII range implied by that regular expression.</span>
</code></div>
  </div>
 </div>
 <a name="107080"></a>
 <div class="note">
  <strong class='user'>megan at voices dot com</strong>
  <a href="#107080" class="date">05-Jan-2012 08:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
"Note: $this is a special variable that can't be assigned."<br />
<br />
While the PHP runtime generates an error if you directly assign $this in code, it doesn't for $$name when name is 'this'.<br />
<br />
<span class="default">&lt;?php<br />
<br />
$this </span><span class="keyword">= </span><span class="string">'text'</span><span class="keyword">; </span><span class="comment">// error<br />
<br />
</span><span class="default">$name </span><span class="keyword">= </span><span class="string">'this'</span><span class="keyword">;<br />
$</span><span class="default">$name </span><span class="keyword">= </span><span class="string">'text'</span><span class="keyword">; </span><span class="comment">// sets $this to 'text'<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="101978"></a>
 <div class="note">
  <strong class='user'>maurizio dot domba at pu dot t-com dot hr</strong>
  <a href="#101978" class="date">21-Jan-2011 02:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you need to check user entered value for a proper PHP variable naming convention you need to add ^ to the above regular expression so that the regular expression should be '^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*'.<br />
<br />
Example<br />
<br />
<span class="default">&lt;?php<br />
$name</span><span class="keyword">=</span><span class="string">"20011aa"</span><span class="keyword">;<br />
if(!</span><span class="default">preg_match</span><span class="keyword">(</span><span class="string">'/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/'</span><span class="keyword">,</span><span class="default">$name</span><span class="keyword">))<br />
&nbsp;&nbsp; echo </span><span class="default">$name</span><span class="keyword">.</span><span class="string">' is not a valid PHP variable name'</span><span class="keyword">;<br />
else<br />
&nbsp;&nbsp; echo </span><span class="default">$name</span><span class="keyword">.</span><span class="string">' is valid PHP variable name'</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Outputs: 2011aa is valid PHP variable name<br />
<br />
but<br />
<br />
<span class="default">&lt;?php<br />
$name</span><span class="keyword">=</span><span class="string">"20011aa"</span><span class="keyword">;<br />
if(!</span><span class="default">preg_match</span><span class="keyword">(</span><span class="string">'/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/'</span><span class="keyword">,</span><span class="default">$name</span><span class="keyword">))<br />
&nbsp;&nbsp; echo </span><span class="default">$name</span><span class="keyword">.</span><span class="string">' is not a valid PHP variable name'</span><span class="keyword">;<br />
else<br />
&nbsp;&nbsp; echo </span><span class="default">$name</span><span class="keyword">.</span><span class="string">' is valid PHP variable name'</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Outputs: 2011aa is not a valid PHP variable name</span>
</code></div>
  </div>
 </div>
 <a name="99873"></a>
 <div class="note">
  <strong class='user'>jeff dot phpnet at tanasity dot com</strong>
  <a href="#99873" class="date">11-Sep-2010 04:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This page should include a note on variable lifecycle:<br />
<br />
Before a variable is used, it has no existence. It is unset. It is possible to check if a variable doesn't exist by using isset(). This returns true provided the variable exists and isn't set to null. With the exception of null, the value a variable holds plays no part in determining whether a variable is set. <br />
<br />
Setting an existing variable to null is a way of unsetting a variable. Another way is variables may be destroyed by using the unset() construct. <br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">print isset(</span><span class="default">$a</span><span class="keyword">); </span><span class="comment">// $a is not set. Prints false. (Or more accurately prints ''.)<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="comment">// isset($b) returns true (or more accurately '1')<br />
</span><span class="default">$c </span><span class="keyword">= array(); </span><span class="comment">// isset($c) returns true<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">; </span><span class="comment">// Now isset($b) returns false;<br />
</span><span class="keyword">unset(</span><span class="default">$c</span><span class="keyword">); </span><span class="comment">// Now isset($c) returns false;<br />
</span><span class="default">?&gt;<br />
</span><br />
is_null() is an equivalent test to checking that isset() is false.<br />
<br />
The first time that a variable is used in a scope, it's automatically created. After this isset is true. At the point at which it is created it also receives a type according to the context.<br />
<br />
<span class="default">&lt;?php<br />
$a_bool </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;&nbsp;&nbsp; </span><span class="comment">// a boolean<br />
</span><span class="default">$a_str </span><span class="keyword">= </span><span class="string">'foo'</span><span class="keyword">;&nbsp; &nbsp; </span><span class="comment">// a string<br />
</span><span class="default">?&gt;<br />
</span><br />
If it is used without having been given a value then it is uninitalized and it receives the default value for the type. The default values are the _empty_ values. E.g&nbsp; Booleans default to FALSE, integers and floats default to zero, strings to the empty string '', arrays to the empty array.<br />
<br />
A variable can be tested for emptiness using empty();<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="comment">//This isset, but is empty<br />
</span><span class="default">?&gt;<br />
</span><br />
Unset variables are also empty.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">empty(</span><span class="default">$vessel</span><span class="keyword">); </span><span class="comment">// returns true. Also $vessel is unset.<br />
</span><span class="default">?&gt;<br />
</span><br />
Everything above applies to array elements too. <br />
<br />
<span class="default">&lt;?php<br />
$item </span><span class="keyword">= array(); <br />
</span><span class="comment">//Now isset($item) returns true. But isset($item['unicorn']) is false.<br />
//empty($item) is true, and so is empty($item['unicorn']<br />
<br />
</span><span class="default">$item</span><span class="keyword">[</span><span class="string">'unicorn'</span><span class="keyword">] = </span><span class="string">''</span><span class="keyword">;<br />
</span><span class="comment">//Now isset($item['unicorn']) is true. And empty($item) is false. <br />
//But empty($item['unicorn']) is still true;<br />
<br />
</span><span class="default">$item</span><span class="keyword">[</span><span class="string">'unicorn'</span><span class="keyword">] = </span><span class="string">'Pink unicorn'</span><span class="keyword">;<br />
</span><span class="comment">//isset($item['unicorn']) is still true. And empty($item) is still false. <br />
//But now empty($item['unicorn']) is false;<br />
</span><span class="default">?&gt;<br />
</span><br />
For arrays, this is important because accessing a non-existent array item can trigger errors; you may want to test arrays and array items for existence with isset before using them.</span>
</code></div>
  </div>
 </div>
 <a name="96594"></a>
 <div class="note">
  <strong class='user'>Edoxile</strong>
  <a href="#96594" class="date">06-Mar-2010 01:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When wanting to switch two variables from content, you can use the XOR operator:<br />
<br />
<span class="default">&lt;?PHP<br />
$a</span><span class="keyword">=</span><span class="default">5</span><span class="keyword">;<br />
</span><span class="default">$b</span><span class="keyword">=</span><span class="default">3</span><span class="keyword">;<br />
<br />
</span><span class="comment">//Please mind the order of these, as it's important for the outcome.<br />
<br />
</span><span class="default">$a</span><span class="keyword">^=</span><span class="default">$b</span><span class="keyword">;<br />
</span><span class="default">$b</span><span class="keyword">^=</span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$a</span><span class="keyword">^=</span><span class="default">$b</span><span class="keyword">;<br />
<br />
echo </span><span class="default">$a</span><span class="keyword">.</span><span class="default">PHP_EOL</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
</span><span class="comment">/* prints:<br />
3<br />
5<br />
*/<br />
</span><span class="default">?&gt;<br />
</span><br />
This will also work on strings, but it won't work on arrays and objects, so for them you'll have to use the serialize() function before the operation, and the unserialize() function after.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.variables.basics&amp;redirect=http://www.php.net/manual/en/language.variables.basics.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.variables.basics&amp;redirect=http://www.php.net/manual/en/language.variables.basics.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.variables.basics.php">show source</a> |
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