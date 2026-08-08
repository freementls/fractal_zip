<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Extending Exceptions - Manual</title>
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
 <link rel="index" href="language.exceptions.php" />
 <link rel="prev" href="language.exceptions.php" />
 <link rel="next" href="language.references.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/exceptions.extending" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.exceptions.extending.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{Z9Y8Y7Q2}" />
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
 <li class="header up"><a href="language.exceptions.php">Exceptions</a></li>
 <li class="active"><a href="language.exceptions.extending.php">Extending Exceptions</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.references.php">References Explained<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.exceptions.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Exceptions</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.exceptions.extending.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.exceptions.extending.php">Brazilian Portuguese</option>
    <option value="zh/language.exceptions.extending.php">Chinese (Simplified)</option>
    <option value="fr/language.exceptions.extending.php">French</option>
    <option value="de/language.exceptions.extending.php">German</option>
    <option value="ja/language.exceptions.extending.php">Japanese</option>
    <option value="pl/language.exceptions.extending.php">Polish</option>
    <option value="ro/language.exceptions.extending.php">Romanian</option>
    <option value="ru/language.exceptions.extending.php">Russian</option>
    <option value="fa/language.exceptions.extending.php">Persian</option>
    <option value="es/language.exceptions.extending.php">Spanish</option>
    <option value="tr/language.exceptions.extending.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.exceptions.extending" class="sect1">
   <h2 class="title">Extending Exceptions</h2>
   <p class="para">
    A User defined Exception class can be defined by extending the built-in
    Exception class. The members and properties below, show what is accessible
    within the child class that derives from the built-in Exception class.
   </p>
   <div class="example" id="example-264">
    <p><strong>Example #1 The Built in Exception class</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">Exception<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;protected&nbsp;</span><span style="color: #0000BB">$message&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Unknown&nbsp;exception'</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;exception&nbsp;message<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">private&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$string</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;__toString&nbsp;cache<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">protected&nbsp;</span><span style="color: #0000BB">$code&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;user&nbsp;defined&nbsp;exception&nbsp;code<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">protected&nbsp;</span><span style="color: #0000BB">$file</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;source&nbsp;filename&nbsp;of&nbsp;exception<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">protected&nbsp;</span><span style="color: #0000BB">$line</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;source&nbsp;line&nbsp;of&nbsp;exception<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">private&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$trace</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;backtrace<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">private&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$previous</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;previous&nbsp;exception&nbsp;if&nbsp;nested&nbsp;exception<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;function&nbsp;</span><span style="color: #0000BB">__construct</span><span style="color: #007700">(</span><span style="color: #0000BB">$message&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">null</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$code&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">Exception&nbsp;$previous&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">null</span><span style="color: #007700">);<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;final&nbsp;private&nbsp;function&nbsp;</span><span style="color: #0000BB">__clone</span><span style="color: #007700">();&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Inhibits&nbsp;cloning&nbsp;of&nbsp;exceptions.<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">final&nbsp;public&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">getMessage</span><span style="color: #007700">();&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;message&nbsp;of&nbsp;exception<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">final&nbsp;public&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">getCode</span><span style="color: #007700">();&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;code&nbsp;of&nbsp;exception<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">final&nbsp;public&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">getFile</span><span style="color: #007700">();&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;source&nbsp;filename<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">final&nbsp;public&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">getLine</span><span style="color: #007700">();&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;source&nbsp;line<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">final&nbsp;public&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">getTrace</span><span style="color: #007700">();&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;an&nbsp;array&nbsp;of&nbsp;the&nbsp;backtrace()<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">final&nbsp;public&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">getPrevious</span><span style="color: #007700">();&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;previous&nbsp;exception<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">final&nbsp;public&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">getTraceAsString</span><span style="color: #007700">();&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;formatted&nbsp;string&nbsp;of&nbsp;trace<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;/*&nbsp;Overrideable&nbsp;*/<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;function&nbsp;</span><span style="color: #0000BB">__toString</span><span style="color: #007700">();&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;formatted&nbsp;string&nbsp;for&nbsp;display<br /></span><span style="color: #007700">}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   <p class="para">
    If a class extends the built-in Exception class and re-defines the <a href="language.oop5.decon.php" class="link">constructor</a>, it is highly recommended
    that it also call <a href="language.oop5.paamayim-nekudotayim.php" class="link">parent::__construct()</a> 
    to ensure all available data has been properly assigned. The <a href="language.oop5.magic.php" class="link">__toString()</a> method can be overridden
    to provide a custom output when the object is presented as a string.
   </p>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     Exceptions cannot be cloned. Attempting to <a href="language.oop5.cloning.php" class="link">clone</a> an Exception will result in a
     fatal <strong><code>E_ERROR</code></strong> error.
    </p>
   </p></blockquote>
   <div class="example" id="example-265">
    <p><strong>Example #2 Extending the Exception class (PHP 5.3.0+)</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">/**<br />&nbsp;*&nbsp;Define&nbsp;a&nbsp;custom&nbsp;exception&nbsp;class<br />&nbsp;*/<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyException&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">Exception<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Redefine&nbsp;the&nbsp;exception&nbsp;so&nbsp;message&nbsp;isn't&nbsp;optional<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;function&nbsp;</span><span style="color: #0000BB">__construct</span><span style="color: #007700">(</span><span style="color: #0000BB">$message</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$code&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">Exception&nbsp;$previous&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">null</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;some&nbsp;code<br />&nbsp;&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;//&nbsp;make&nbsp;sure&nbsp;everything&nbsp;is&nbsp;assigned&nbsp;properly<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">parent</span><span style="color: #007700">::</span><span style="color: #0000BB">__construct</span><span style="color: #007700">(</span><span style="color: #0000BB">$message</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$code</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$previous</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;custom&nbsp;string&nbsp;representation&nbsp;of&nbsp;object<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;function&nbsp;</span><span style="color: #0000BB">__toString</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">__CLASS__&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">":&nbsp;[</span><span style="color: #007700">{</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">code</span><span style="color: #007700">}</span><span style="color: #DD0000">]:&nbsp;</span><span style="color: #007700">{</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">message</span><span style="color: #007700">}</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">customFunction</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"A&nbsp;custom&nbsp;function&nbsp;for&nbsp;this&nbsp;type&nbsp;of&nbsp;exception\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /><br /></span><span style="color: #FF8000">/**<br />&nbsp;*&nbsp;Create&nbsp;a&nbsp;class&nbsp;to&nbsp;test&nbsp;the&nbsp;exception<br />&nbsp;*/<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">TestException<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;</span><span style="color: #0000BB">THROW_NONE&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;</span><span style="color: #0000BB">THROW_CUSTOM&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;</span><span style="color: #0000BB">THROW_DEFAULT&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">__construct</span><span style="color: #007700">(</span><span style="color: #0000BB">$avalue&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">self</span><span style="color: #007700">::</span><span style="color: #0000BB">THROW_NONE</span><span style="color: #007700">)&nbsp;{<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;switch&nbsp;(</span><span style="color: #0000BB">$avalue</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;case&nbsp;</span><span style="color: #0000BB">self</span><span style="color: #007700">::</span><span style="color: #0000BB">THROW_CUSTOM</span><span style="color: #007700">:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;throw&nbsp;custom&nbsp;exception<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">throw&nbsp;new&nbsp;</span><span style="color: #0000BB">MyException</span><span style="color: #007700">(</span><span style="color: #DD0000">'1&nbsp;is&nbsp;an&nbsp;invalid&nbsp;parameter'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">5</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;case&nbsp;</span><span style="color: #0000BB">self</span><span style="color: #007700">::</span><span style="color: #0000BB">THROW_DEFAULT</span><span style="color: #007700">:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;throw&nbsp;default&nbsp;one.<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">throw&nbsp;new&nbsp;</span><span style="color: #0000BB">Exception</span><span style="color: #007700">(</span><span style="color: #DD0000">'2&nbsp;is&nbsp;not&nbsp;allowed&nbsp;as&nbsp;a&nbsp;parameter'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">6</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;default:&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;No&nbsp;exception,&nbsp;object&nbsp;will&nbsp;be&nbsp;created.<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">var&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$avalue</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /><br /></span><span style="color: #FF8000">//&nbsp;Example&nbsp;1<br /></span><span style="color: #007700">try&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$o&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">TestException</span><span style="color: #007700">(</span><span style="color: #0000BB">TestException</span><span style="color: #007700">::</span><span style="color: #0000BB">THROW_CUSTOM</span><span style="color: #007700">);<br />}&nbsp;catch&nbsp;(</span><span style="color: #0000BB">MyException&nbsp;$e</span><span style="color: #007700">)&nbsp;{&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Will&nbsp;be&nbsp;caught<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"Caught&nbsp;my&nbsp;exception\n"</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$e</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$e</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">customFunction</span><span style="color: #007700">();<br />}&nbsp;catch&nbsp;(</span><span style="color: #0000BB">Exception&nbsp;$e</span><span style="color: #007700">)&nbsp;{&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Skipped<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"Caught&nbsp;Default&nbsp;Exception\n"</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$e</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;Continue&nbsp;execution<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$o</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;Null<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"\n\n"</span><span style="color: #007700">;<br /><br /><br /></span><span style="color: #FF8000">//&nbsp;Example&nbsp;2<br /></span><span style="color: #007700">try&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$o&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">TestException</span><span style="color: #007700">(</span><span style="color: #0000BB">TestException</span><span style="color: #007700">::</span><span style="color: #0000BB">THROW_DEFAULT</span><span style="color: #007700">);<br />}&nbsp;catch&nbsp;(</span><span style="color: #0000BB">MyException&nbsp;$e</span><span style="color: #007700">)&nbsp;{&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Doesn't&nbsp;match&nbsp;this&nbsp;type<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"Caught&nbsp;my&nbsp;exception\n"</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$e</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$e</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">customFunction</span><span style="color: #007700">();<br />}&nbsp;catch&nbsp;(</span><span style="color: #0000BB">Exception&nbsp;$e</span><span style="color: #007700">)&nbsp;{&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Will&nbsp;be&nbsp;caught<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"Caught&nbsp;Default&nbsp;Exception\n"</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$e</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;Continue&nbsp;execution<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$o</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;Null<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"\n\n"</span><span style="color: #007700">;<br /><br /><br /></span><span style="color: #FF8000">//&nbsp;Example&nbsp;3<br /></span><span style="color: #007700">try&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$o&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">TestException</span><span style="color: #007700">(</span><span style="color: #0000BB">TestException</span><span style="color: #007700">::</span><span style="color: #0000BB">THROW_CUSTOM</span><span style="color: #007700">);<br />}&nbsp;catch&nbsp;(</span><span style="color: #0000BB">Exception&nbsp;$e</span><span style="color: #007700">)&nbsp;{&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Will&nbsp;be&nbsp;caught<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"Default&nbsp;Exception&nbsp;caught\n"</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$e</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;Continue&nbsp;execution<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$o</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;Null<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"\n\n"</span><span style="color: #007700">;<br /><br /><br /></span><span style="color: #FF8000">//&nbsp;Example&nbsp;4<br /></span><span style="color: #007700">try&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$o&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">TestException</span><span style="color: #007700">();<br />}&nbsp;catch&nbsp;(</span><span style="color: #0000BB">Exception&nbsp;$e</span><span style="color: #007700">)&nbsp;{&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Skipped,&nbsp;no&nbsp;exception<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"Default&nbsp;Exception&nbsp;caught\n"</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$e</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;Continue&nbsp;execution<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$o</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;TestException<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"\n\n"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <blockquote class="note"><p><strong class="note">Note</strong>: 
     <p class="para">
      Versions of PHP 5, prior to PHP 5.3.0 do not support nesting of exceptions.
      The following code fragment can be used as a replacement MyException class
      if you wish to run this example.
     </p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">/**<br />&nbsp;*&nbsp;Define&nbsp;a&nbsp;custom&nbsp;exception&nbsp;class<br />&nbsp;*/<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyException&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">Exception<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Redefine&nbsp;the&nbsp;exception&nbsp;so&nbsp;message&nbsp;isn't&nbsp;optional<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;function&nbsp;</span><span style="color: #0000BB">__construct</span><span style="color: #007700">(</span><span style="color: #0000BB">$message</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$code&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;some&nbsp;code<br />&nbsp;&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;//&nbsp;make&nbsp;sure&nbsp;everything&nbsp;is&nbsp;assigned&nbsp;properly<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">parent</span><span style="color: #007700">::</span><span style="color: #0000BB">__construct</span><span style="color: #007700">(</span><span style="color: #0000BB">$message</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$code</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;custom&nbsp;string&nbsp;representation&nbsp;of&nbsp;object<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;function&nbsp;</span><span style="color: #0000BB">__toString</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">__CLASS__&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">":&nbsp;[</span><span style="color: #007700">{</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">code</span><span style="color: #007700">}</span><span style="color: #DD0000">]:&nbsp;</span><span style="color: #007700">{</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">message</span><span style="color: #007700">}</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">customFunction</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"A&nbsp;custom&nbsp;function&nbsp;for&nbsp;this&nbsp;type&nbsp;of&nbsp;exception\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </p></blockquote>
   </div>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.references.php">References Explained<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.exceptions.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Exceptions</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.exceptions.extending.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.exceptions.extending&amp;redirect=@w{Z9Y8Y7Q2}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.exceptions.extending&amp;redirect=@w{Z9Y8Y7Q2}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Extending Exceptions</strong>
 </div><div id="allnotes">
 <a name="106168"></a>
 <div class="note">
  <strong class='user'>Dor</strong>
  <a href="#106168" class="date">15-Oct-2011 02:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It's important to note that subclasses of the Exception class will be caught by the default Exception handler<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; &nbsp; * NewException<br />
&nbsp;&nbsp; &nbsp; * Extends the Exception class so that the $message parameter is now mendatory.<br />
&nbsp;&nbsp; &nbsp; * <br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">class </span><span class="default">NewException </span><span class="keyword">extends </span><span class="default">Exception </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//$message is now not optional, just for the extension.<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$message</span><span class="keyword">, </span><span class="default">$code </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">, </span><span class="default">Exception $previous </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">parent</span><span class="keyword">::</span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$message</span><span class="keyword">, </span><span class="default">$code</span><span class="keyword">, </span><span class="default">$previous</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; &nbsp; * TestException<br />
&nbsp;&nbsp; &nbsp; * Tests and throws Exceptions.<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">class </span><span class="default">TestException </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; const </span><span class="default">NONE </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; const </span><span class="default">NORMAL </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; const </span><span class="default">CUSTOM </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$type </span><span class="keyword">= </span><span class="default">self</span><span class="keyword">::</span><span class="default">NONE</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; switch (</span><span class="default">$type</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">1</span><span class="keyword">: <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; throw new </span><span class="default">Exception</span><span class="keyword">(</span><span class="string">'Normal Exception'</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">2</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; throw new </span><span class="default">NewException</span><span class="keyword">(</span><span class="string">'Custom Exception'</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; default:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">0</span><span class="keyword">; </span><span class="comment">//No exception is thrown.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; try {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$t </span><span class="keyword">= new </span><span class="default">TestException</span><span class="keyword">(</span><span class="default">TestException</span><span class="keyword">::</span><span class="default">CUSTOM</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; catch (</span><span class="default">Exception $e</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$e</span><span class="keyword">); </span><span class="comment">//Exception Caught<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp;&nbsp; <br />
</span><span class="default">?&gt;<br />
</span><br />
Note that if an Exception is caught once, it won't be caught again (even for a more specific handler).</span>
</code></div>
  </div>
 </div>
 <a name="96289"></a>
 <div class="note">
  <strong class='user'>joechrz at gmail dot com</strong>
  <a href="#96289" class="date">18-Feb-2010 03:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It's important to note some unexpected behavior when overriding the __toString method of an Exception.&nbsp; The default PHP exception handler will truncate the result of the __toString method to the number of bytes specified by log_errors_max_len in php.ini.&nbsp; <br />
<br />
To get around this problem, you need to either change the value of log_errors_max_len:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// recommended: disable error logging<br />
&nbsp;&nbsp;&nbsp; // so the log files don't become bloated from huge <br />
&nbsp;&nbsp;&nbsp; // exception strings<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">ini_set</span><span class="keyword">(</span><span class="string">'log_errors'</span><span class="keyword">,</span><span class="string">'off'</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// log_errors_max_len = infinite length<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">ini_set</span><span class="keyword">(</span><span class="string">"log_errors_max_len"</span><span class="keyword">,</span><span class="default">0</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
or specify a custom exception handler:<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">long_exception_handler</span><span class="keyword">(</span><span class="default">$exception</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// for compatibility, call __toString<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">echo </span><span class="default">$exception</span><span class="keyword">-&gt;</span><span class="default">__toString</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">set_exception_handler</span><span class="keyword">(</span><span class="string">'long_exception_handler'</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="95403"></a>
 <div class="note">
  <strong class='user'>iamhiddensomewhere at gmail dot com</strong>
  <a href="#95403" class="date">31-Dec-2009 03:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As previously noted exception linking was recently added (and what a god-send it is, it certainly makes layer abstraction (and, by association, exception tracking) easier).<br />
<br />
Since &lt;5.3 was lacking this useful feature I took some initiative and creating a custom exception class that all of my exceptions inherit from:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">SystemException </span><span class="keyword">extends </span><span class="default">Exception<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$previous</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$message</span><span class="keyword">, </span><span class="default">$code </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">, </span><span class="default">Exception $previous </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">parent</span><span class="keyword">::</span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$message</span><span class="keyword">, </span><span class="default">$code</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (!</span><span class="default">is_null</span><span class="keyword">(</span><span class="default">$previous</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this </span><span class="keyword">-&gt; </span><span class="default">previous </span><span class="keyword">= </span><span class="default">$previous</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getPrevious</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this </span><span class="keyword">-&gt; </span><span class="default">previous</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Hope you find it useful.</span>
</code></div>
  </div>
 </div>
 <a name="94780"></a>
 <div class="note">
  <strong class='user'>sapphirepaw.org</strong>
  <a href="#94780" class="date">24-Nov-2009 07:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Support for exception linking was added in PHP 5.3.0. The getPrevious() method and the $previous argument to the constructor are not available on any built-in exceptions in older versions of PHP.</span>
</code></div>
  </div>
 </div>
 <a name="88253"></a>
 <div class="note">
  <strong class='user'>paragdiwan at gmail dot com</strong>
  <a href="#88253" class="date">17-Jan-2009 01:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I have written similar simple custom exception class. Helpful for newbie.<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; This is written for overriding the exceptions.<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; custom exception class<br />
&nbsp;&nbsp;&nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">error_reporting</span><span class="keyword">(</span><span class="default">E_ALL</span><span class="keyword">-</span><span class="default">E_NOTICE</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; class </span><span class="default">myCustomException </span><span class="keyword">extends </span><span class="default">Exception <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$message</span><span class="keyword">, </span><span class="default">$code</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">parent</span><span class="keyword">::</span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$message</span><span class="keyword">,</span><span class="default">$code</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }&nbsp; &nbsp; <br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">__toString</span><span class="keyword">()<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"&lt;b style='color:red'&gt;"</span><span class="keyword">.</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">message</span><span class="keyword">.</span><span class="string">"&lt;/b&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; class </span><span class="default">testException <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$x</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">x</span><span class="keyword">=</span><span class="default">$x</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function </span><span class="default">see</span><span class="keyword">() <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">x</span><span class="keyword">==</span><span class="default">9 </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; throw new </span><span class="default">myCustomException</span><span class="keyword">(</span><span class="string">"i didnt like it"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">testException</span><span class="keyword">(</span><span class="default">9</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; try{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">see</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; catch(</span><span class="default">myCustomException $e</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$e</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.exceptions.extending&amp;redirect=@w{Z9Y8Y7Q2}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.exceptions.extending&amp;redirect=@w{Z9Y8Y7Q2}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.exceptions.extending.php">show source</a> |
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