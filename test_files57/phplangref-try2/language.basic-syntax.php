<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Basic syntax - Manual</title>
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
 <link rel="index" href="langref.php" />
 <link rel="prev" href="langref.php" />
 <link rel="next" href="language.basic-syntax.phptags.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/basic-syntax" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/language.basic-syntax.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/language.basic-syntax.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.basic-syntax.php" />
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
 <li class="active"><a href="language.basic-syntax.php">Basic syntax</a></li>
 <li><a href="language.types.php">Types</a></li>
 <li><a href="language.variables.php">Variables</a></li>
 <li><a href="language.constants.php">Constants</a></li>
 <li><a href="language.expressions.php">Expressions</a></li>
 <li><a href="language.operators.php">Operators</a></li>
 <li><a href="language.control-structures.php">Control Structures</a></li>
 <li><a href="language.functions.php">Functions</a></li>
 <li><a href="language.oop5.php">Classes and Objects</a></li>
 <li><a href="language.namespaces.php">Namespaces</a></li>
 <li><a href="language.exceptions.php">Exceptions</a></li>
 <li><a href="language.references.php">References Explained</a></li>
 <li><a href="reserved.variables.php">Predefined Variables</a></li>
 <li><a href="reserved.exceptions.php">Predefined Exceptions</a></li>
 <li><a href="reserved.interfaces.php">Predefined Interfaces</a></li>
 <li><a href="context.php">Context options and parameters</a></li>
 <li><a href="wrappers.php">Supported Protocols and Wrappers</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.basic-syntax.phptags.php">PHP tags<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="langref.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Language Reference</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.basic-syntax.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.basic-syntax.php">Brazilian Portuguese</option>
    <option value="zh/language.basic-syntax.php">Chinese (Simplified)</option>
    <option value="fr/language.basic-syntax.php">French</option>
    <option value="de/language.basic-syntax.php">German</option>
    <option value="ja/language.basic-syntax.php">Japanese</option>
    <option value="pl/language.basic-syntax.php">Polish</option>
    <option value="ro/language.basic-syntax.php">Romanian</option>
    <option value="ru/language.basic-syntax.php">Russian</option>
    <option value="fa/language.basic-syntax.php">Persian</option>
    <option value="es/language.basic-syntax.php">Spanish</option>
    <option value="tr/language.basic-syntax.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.basic-syntax" class="chapter">
  <h1>Basic syntax</h1>
<h2>Table of Contents</h2><ul class="chunklist chunklist_chapter"><li><a href="language.basic-syntax.phptags.php">PHP tags</a></li><li><a href="language.basic-syntax.phpmode.php">Escaping from HTML</a></li><li><a href="language.basic-syntax.instruction-separation.php">Instruction separation</a></li><li><a href="language.basic-syntax.comments.php">Comments</a></li></ul>

  
  
  
  
 </div>
<br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.basic-syntax.phptags.php">PHP tags<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="langref.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Language Reference</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.basic-syntax.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.basic-syntax&amp;redirect=http://www.php.net/manual/en/language.basic-syntax.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.basic-syntax&amp;redirect=http://www.php.net/manual/en/language.basic-syntax.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Basic syntax</strong>
 </div><div id="allnotes">
 <a name="100178"></a>
 <div class="note">
  <strong class='user'>php_engineer_bk at yahoo dot com</strong>
  <a href="#100178" class="date">29-Sep-2010 10:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
all syntax:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if(</span><span class="default">$true</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"true"</span><span class="keyword">;<br />
}<br />
else<br />
{<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"false"</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if(</span><span class="default">$true</span><span class="keyword">)<br />
&nbsp;&nbsp; echo </span><span class="string">"true"</span><span class="keyword">;<br />
else<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"false"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if(</span><span class="default">$true</span><span class="keyword">):<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"true"</span><span class="keyword">;<br />
else<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"false"</span><span class="keyword">;<br />
endif;<br />
</span><span class="default">?&gt;<br />
</span><br />
Iranian php programming(farhad zandmoghadam)</span>
</code></div>
  </div>
 </div>
 <a name="84057"></a>
 <div class="note">
  <strong class='user'>mattsch at gmail dot com</strong>
  <a href="#84057" class="date">25-Jun-2008 01:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Less is more.&nbsp; The shortest and easiest way to deal with the xml tag problem assuming short tags is enabled and you don't care to listen to people who want you to always use the full php tag is this:<br />
<br />
&lt;&lt;??&gt;?xml version="1.0" encoding="utf-8"?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="74920"></a>
 <div class="note">
  <strong class='user'>Tona at spikesource dot com</strong>
  <a href="#74920" class="date">03-May-2007 03:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Jascam: Try to find more resourceful information to make your point. Your lack of ability to understand more complex concepts is not enough to diminish such a popular language as PHP. Note also that php is not replacing html but complementing it.</span>
</code></div>
  </div>
 </div>
 <a name="73627"></a>
 <div class="note">
  <strong class='user'>Geekman at Textbook Torrents dot com</strong>
  <a href="#73627" class="date">04-Mar-2007 05:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Regarding the comment by rosswilliams at advocacytechnologies dot org:<br />
<br />
Your suspicion is correct. The following all behave exactly the same:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// output the answer by escaping<br />
</span><span class="keyword">if (</span><span class="default">$true_or_false</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">?&gt;<br />
</span>&nbsp; &nbsp; &lt;p&gt;The value of $true_or_false is true.&lt;/p&gt;<br />
&nbsp;&nbsp;&nbsp; <span class="default">&lt;?php<br />
</span><span class="keyword">} else {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">?&gt;<br />
</span>&nbsp; &nbsp; &lt;p&gt;The value of $true_or_false is false.&lt;/p&gt;<br />
&nbsp;&nbsp;&nbsp; <span class="default">&lt;?php<br />
</span><span class="keyword">}<br />
<br />
</span><span class="comment">// use echo to do the same thing - more effecient and easier to read in my opinion<br />
</span><span class="keyword">if (</span><span class="default">$true_or_false</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'&lt;p&gt;The value of $true_or_false is true.&lt;/p&gt;'</span><span class="keyword">;<br />
} else {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'&lt;p&gt;The value of $true_or_false is false.&lt;/p&gt;'</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">// use ? : operators on entire string<br />
</span><span class="keyword">echo (</span><span class="default">$true_or_false</span><span class="keyword">) ? </span><span class="string">'&lt;p&gt;The value of $true_or_false is true.&lt;/p&gt;' </span><span class="keyword">: </span><span class="string">'&lt;p&gt;The value of $true_or_false is false.&lt;/p&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="comment">// use ? : operators only on the pertinent bit, to save space<br />
</span><span class="keyword">echo </span><span class="string">'&lt;p&gt;The value of $true_or_false is ' </span><span class="keyword">. ((</span><span class="default">$true_or_false</span><span class="keyword">) ? </span><span class="string">'true' </span><span class="keyword">: </span><span class="string">'false'</span><span class="keyword">) . </span><span class="string">'.&lt;/p&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="68336"></a>
 <div class="note">
  <strong class='user'>alfridus</strong>
  <a href="#68336" class="date">23-Jul-2006 04:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Only this work:<br />
<br />
<span class="default">&lt;?php <br />
$xml </span><span class="keyword">= </span><span class="string">'&lt;?xml version="1.0" encoding="UTF-8" standalone="no"?&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$xml</span><span class="keyword">;<br />
&nbsp;</span><span class="default">?&gt;<br />
</span><br />
with space after '&lt;?php' and before ' ?&gt;', no spacing between<br />
'&lt;?xml' and a semicolon after '"no"?&gt;';'.</span>
</code></div>
  </div>
 </div>
 <a name="63861"></a>
 <div class="note">
  <strong class='user'>brettz9 at yahoo dot com</strong>
  <a href="#63861" class="date">02-Apr-2006 08:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I've essentially tried to synthesize this<br />
discussion at <br />
<a href="http://en.wikibooks.org/wiki/Programming:" rel="nofollow" target="_blank">http://en.wikibooks.org/wiki/Programming:</a><br />
Complete_PHP/Escaping_from_HTML<br />
<br />
One point not brought up yet...<br />
<br />
The HEREDOC problem with some text editors<br />
may be fixable in at least some text editors<br />
by adding a comment with a quotation mark<br />
as such afterwards (albeit necessarily on a<br />
new line):<br />
<br />
<span class="default">&lt;?php<br />
<br />
$version </span><span class="keyword">= </span><span class="string">"1.0"</span><span class="keyword">;<br />
<br />
print &lt;&lt;&lt;HERE<br />
</span><span class="default">&lt;?xml version="<br />
</span><span class="keyword">HERE;<br />
</span><span class="comment">//"<br />
<br />
</span><span class="keyword">print </span><span class="default">$version</span><span class="keyword">.</span><span class="string">"\"?&gt;"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="60798"></a>
 <div class="note">
  <strong class='user'>Christoph</strong>
  <a href="#60798" class="date">16-Jan-2006 05:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's an inspiration on how to quickly fix all scripts relying on short_open_tag being enabled:<br />
<br />
find -name '*.php' | xargs perl -pi -e 's/&lt;\?= ?(.*?) ?\?&gt;/<span class="default">&lt;?php </span><span class="keyword">echo($</span><span class="default">1</span><span class="keyword">); </span><span class="default">?&gt;</span>/g'<br />
find -name '*.php' | xargs perl -pi -e 's/&lt;\?/&lt;?php/g'<br />
find -name '*.php' | xargs perl -pi -e 's/&lt;\?phpphp/&lt;?php/g'</span>
</code></div>
  </div>
 </div>
 <a name="59663"></a>
 <div class="note">
  <strong class='user'>Michael Newton (http://mike.eire.ca/)</strong>
  <a href="#59663" class="date">12-Dec-2005 03:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The XML declaration does not need to be handled specially.<br />
<br />
You should output it via an echo statement, in case your code is ever used on a server that is (poorly) configured to use short open tags.<br />
<br />
But there's no need to treat the ?&gt; at the end of the string specially.&nbsp; That's because it's in a string.&nbsp; The only thing PHP ever looks for in a string is \ or $ (the latter only in double-quoted strings.)<br />
<br />
I have never had need for the following, as some have suggested below:<br />
<br />
<span class="default">&lt;?php<br />
$xml</span><span class="keyword">=</span><span class="default">rawurldecode</span><span class="keyword">(</span><span class="string">'%3C%3Fxml%20version%3D%221.0%22%3F%3E'</span><span class="keyword">);<br />
echo(</span><span class="default">$xml</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php </span><span class="keyword">echo </span><span class="string">'&lt;?xml version="1.0" ?'</span><span class="keyword">.</span><span class="string">'&gt;' </span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php </span><span class="keyword">echo </span><span class="string">"&lt;?xml version=\"1.0\"\x3F&gt;" </span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="57086"></a>
 <div class="note">
  <strong class='user'>php [AT] jsomers [DOT] be</strong>
  <a href="#57086" class="date">23-Sep-2005 12:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PEAR states:<br />
<br />
Always use <span class="default">&lt;?php ?&gt;</span> to delimit PHP code, not the &lt;? ?&gt; shorthand. This is required for PEAR compliance and is also the most portable way to include PHP code on differing operating systems and setups.<br />
<br />
It are these small things that enhance readability in group projects, or libraries.</span>
</code></div>
  </div>
 </div>
 <a name="55058"></a>
 <div class="note">
  <strong class='user'>pablo [] littleQ.net</strong>
  <a href="#55058" class="date">24-Jul-2005 02:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just another more "feature" of IE...<br />
<br />
Content-Disposition: attachment; filename=\"__FILE__\";<br />
<br />
__FILE__ can't have spaces or :<br />
<br />
Regards</span>
</code></div>
  </div>
 </div>
 <a name="54172"></a>
 <div class="note">
  <strong class='user'>01karlo at gmail dot com</strong>
  <a href="#54172" class="date">25-Jun-2005 08:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Or, use the following:<br />
<br />
<span class="default">&lt;?php<br />
$xml</span><span class="keyword">=</span><span class="default">rawurldecode</span><span class="keyword">(</span><span class="string">'%3C%3Fxml%20version%3D%221.0%22%3F%3E'</span><span class="keyword">);<br />
echo(</span><span class="default">$xml</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
What is does it the value of the variable $xml is the RAW Url Encoded version of the XML thing.<br />
Then it decodes it and echo it to the visitor.</span>
</code></div>
  </div>
 </div>
 <a name="51513"></a>
 <div class="note">
  <strong class='user'>p o r g e s at the gmail dot com server</strong>
  <a href="#51513" class="date">01-Apr-2005 08:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
mike at skew dot org, I believe the differentiation is that "x"-"m"-"l" as a PI target is explicitly excluded from the definition of processing instructions.</span>
</code></div>
  </div>
 </div>
 <a name="51374"></a>
 <div class="note">
  <strong class='user'>Lachlan Hunt</strong>
  <a href="#51374" class="date">28-Mar-2005 09:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The person that suggested the use of this meta element above is wrong:<br />
<br />
&lt;meta http-equiv="Content-Type" content="application/xml+xhtml; charset=UTF-8" /&gt;<br />
<br />
That meta element and the XML declaration serve completely different purposes, and that meta element should not be used.&nbsp; Such information should be set using the HTTP Content-Type header (see the header() function).<br />
<br />
Any XHTML page that just uses that meta element without proper HTTP Content-Type header, will be processed as text/html by browsers regardless, and when the HTTP headers do serve as application/xhtml+xml (or other XML MIME type), that charset parameter in the meta element will be ignored.</span>
</code></div>
  </div>
 </div>
 <a name="46746"></a>
 <div class="note">
  <strong class='user'>mike at skew dot org</strong>
  <a href="#46746" class="date">21-Oct-2004 04:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
mart3862 mentions "XML processing instructions" and quotes their syntax from the spec, but is mistaken in using<br />
<br />
&lt;?xml version="1.0" ...?&gt;<br />
<br />
as an example. This little bit of markup that appears at the beginning of an XML file is in fact not a processing instruction at all; it is an "XML declaration" -- or, if it appears in an entity other than the main document, a "text declaration". All three constructs are formatted slightly differently, although they all do begin and end with the same.<br />
<br />
The difference between a processing instruction, an XML declaration, or a text declaration is more than just a matter of subtle differences in syntax, though. A processing instruction embodies exactly two opaque, author-defined pieces of information (a 'target' and an 'instruction') that are considered to be part of the document's logical structure and that are thus made available to an application by the XML parser. An XML or text declaration, on the other hand, contains one to three specific pieces of information (version, encoding, standalone status), each with a well-defined meaning. This info provides cues to the parser to help it know how to read the file; it is not considered part of the document's logical structure and is not made available to the application.</span>
</code></div>
  </div>
 </div>
 <a name="46708"></a>
 <div class="note">
  <strong class='user'>stooges_cubed at racerx dot net</strong>
  <a href="#46708" class="date">20-Oct-2004 01:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In the note above about escaping XML/PHP style &lt;?xml tags, the following code was used:<br />
<br />
<span class="default">&lt;?php&nbsp; </span><span class="comment">// Html safe containers<br />
<br />
&nbsp;&nbsp; </span><span class="keyword">echo &lt;&lt;&lt;EOD<br />
</span><span class="default">&lt;?xml version="1.0"?&gt;<br />
...all sorts of XML goes here...<br />
Nothing will affect the output of this code until:<br />
</span><span class="keyword">EOD;<br />
</span><span class="default">?&gt;<br />
</span><br />
EOD is just an example stop/start name.<br />
<br />
This works too:<br />
<br />
<span class="default">&lt;?php&nbsp; </span><span class="comment">// Html safe containers<br />
<br />
&nbsp; </span><span class="default">$myOutput </span><span class="keyword">= &lt;&lt;&lt;MYHTMLSAFEOUTPUT<br />
</span><span class="default">&lt;?xml version="1.0"?&gt;<br />
&lt;html&gt;<br />
&nbsp; &lt;title&gt;PHP Example&lt;/title&gt;<br />
&nbsp; &lt;body&gt;<br />
&nbsp;&nbsp; &lt;p&gt;...all sorts goes here...&lt;/p&gt;<br />
&nbsp; &lt;/body&gt;<br />
&lt;/html&gt;<br />
</span><span class="keyword">MYHTMLSAFEOUTPUT;<br />
<br />
echo </span><span class="default">$myOutput</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Only disadvantage of using this is that all the code highlighting programs I've seen never get it right, making your code look eronous in the majority of viewers.<br />
<br />
Another alternative is to keep the XML / HTML in a separate include file and read in when needed. I don't know how efficient/inefficient this is for (idiots like yourselves) small amounts of text.<br />
<br />
xmlheader.txt:<br />
&lt;?xml version="1.0"?&gt;<br />
<br />
mypage.php:<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">include(</span><span class="string">"xmlheader.txt"</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="42055"></a>
 <div class="note">
  <strong class='user'>crtrue at coastal dot edu</strong>
  <a href="#42055" class="date">30-Apr-2004 11:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Although you can use the above methods to pass a document off as a valid for the W3C parser, a simpler-and-perfectly-legal method of doing so is to simple declare the document type in a meta tag. Something along these lines (mind the values in 'content' - I haven't personally used the Content-Type method in awhile):<br />
<br />
&lt;meta http-equiv="Content-Type" content="application/xml+xhtml; charset=UTF-8" /&gt;<br />
<br />
Of course if you're using just XML, and don't use such functions, then the above methods will work just as fine.</span>
</code></div>
  </div>
 </div>
 <a name="41654"></a>
 <div class="note">
  <strong class='user'>mart3862 at yahoo dot com dot au</strong>
  <a href="#41654" class="date">18-Apr-2004 09:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Now the ultimate truth on how you should output xml processing instructions:<br />
<br />
There have been several posts suggesting ways to include the text &lt;?xml version="1.0" encoding="utf-8"?&gt; in your output when short_tags is turned on, but only the following should be used:<br />
<br />
<span class="default">&lt;?php </span><span class="keyword">echo </span><span class="string">'&lt;?xml version="1.0" ?'</span><span class="keyword">.</span><span class="string">'&gt;' </span><span class="default">?&gt;<br />
</span>or<br />
<span class="default">&lt;?php </span><span class="keyword">echo </span><span class="string">"&lt;?xml version=\"1.0\"\x3F&gt;" </span><span class="default">?&gt;<br />
</span><br />
Using one of these methods, and not making use of short tags, means your source code will also be a valid XML document, which allows you to do many things with it such as validation, XSLT translations, etc, as well as allowing your text editor to parse your code for syntax colouring.&nbsp; Every PHP tag will simply be interpreted as an XML processing instruction (commonly referred to as PI).<br />
<br />
The reason why all the other suggested methods are not advisable is because they contain the characters ?&gt; inside the PHP tag, which the XML parser will interpret as the end of the processing instruction.<br />
<br />
A processing instruction is defined in XML as:<br />
<br />
PI ::= '&lt;?' PITarget (S (Char* - (Char* '?&gt;' Char*)))? '?&gt;' <br />
<br />
In other words, it explicitly forbids the characters ?&gt; to occur together within a processing instruction, unless they are delimiting the end of the tag.&nbsp; It also requires a PITarget (an identifier starting with a letter) immediately after the initial start delimiter, which means that all short tag formats are also invalid XML.<br />
<br />
Following these guidelines will result in code that is portable to servers with any configuration and allow you perform many useful tasks on your XML or XHTML source documents.&nbsp; Even if you do not intend to validate or translate your source documents, and you can ignore some incorrect syntax colouring in your text editor, it is still best to get into good habits early.</span>
</code></div>
  </div>
 </div>
 <a name="40097"></a>
 <div class="note">
  <strong class='user'>Anon</strong>
  <a href="#40097" class="date">21-Feb-2004 06:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Yet another way of adding the XML processing instruction is to use:<br />
<br />
<span class="default">&lt;?php </span><span class="keyword">echo </span><span class="string">'&lt;?xml version="1.0" ?'</span><span class="keyword">.</span><span class="string">'&gt;' </span><span class="default">?&gt;<br />
</span><br />
Because the ? and &gt; are separated, the parser will not terminate before it is supposed to.<br />
<br />
As a side note, the W3C's parser seems to recognise this method (assuming it even checks for the PI).</span>
</code></div>
  </div>
 </div>
 <a name="39656"></a>
 <div class="note">
  <strong class='user'>TarquinWJ</strong>
  <a href="#39656" class="date">06-Feb-2004 06:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Not spotted any messages like this one - delete it if there was one.<br />
<br />
My hosting service allows &lt;? and ?&gt;, but I like to use valid XHTML, so I came up with this simple solution:<br />
<br />
It is possible to use the short tags &lt;? ?&gt; with XHTML or XML documents. The only problem is that X(HT)ML requires a declaration using &lt;? and ?&gt;<br />
<br />
&lt;?xml version="1.0" encoding="UTF-8"?&gt;<br />
<br />
To avoid the problem, simply replace &lt;? with &lt;&lt;? ?&gt;?<br />
and ?&gt; with ?&lt;? ?&gt;&gt;<br />
<br />
&lt;&lt;? ?&gt;?xml version="1.0" encoding="UTF-8"?&lt;? ?&gt;&gt;<br />
<br />
This inserts a blank piece of PHP in between the &lt; and ?, and when parsed will output the regular tag<br />
&lt;?xml version="1.0" encoding="UTF-8"?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="38400"></a>
 <div class="note">
  <strong class='user'>mwild at iee dot NO_SP_AM dot org</strong>
  <a href="#38400" class="date">19-Dec-2003 05:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The text between &lt;script&gt; and &lt;/script&gt; in XHTML is PCDATA, so &lt;&nbsp; and &amp; characters in it should be interpreted as markup. This is a bit limiting for PHP, which is often used to output tags, though you can of course use &amp;lt; and &amp;amp; instead. To avoid that, which makes your code look peculiar and is easy to forget to do, you can mark the PHP as CDATA, eg :<br />
<br />
<span class="default">&lt;script language="PHP"&gt;<br />
</span><span class="comment">//&lt;![CDATA[ <br />
</span><span class="keyword">echo(</span><span class="string">'Today is &lt;b&gt;'</span><span class="keyword">.</span><span class="default">date</span><span class="keyword">(</span><span class="string">'l F jS'</span><span class="keyword">).</span><span class="string">'&lt;/b&gt;'</span><span class="keyword">);<br />
</span><span class="comment">//]]&gt;<br />
</span><span class="default">&lt;/script&gt;<br />
</span><br />
If you don't do this, and your code contains &lt; or &amp;, it should be rejected by an XHTML validator.</span>
</code></div>
  </div>
 </div>
 <a name="38084"></a>
 <div class="note">
  <strong class='user'>johnbeech at (not saying) mkv25 dot net</strong>
  <a href="#38084" class="date">07-Dec-2003 04:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In the note above about escaping XML/PHP style &lt;?xml tags, the following code was used:<br />
<br />
<span class="default">&lt;?php&nbsp; </span><span class="comment">// Html safe containers<br />
<br />
&nbsp;&nbsp; </span><span class="keyword">echo &lt;&lt;&lt;EOD<br />
</span><span class="default">&lt;?xml version="1.0"?&gt;<br />
...all sorts of XML goes here...<br />
Nothing will affect the output of this code until:<br />
&nbsp;&nbsp; EOD;<br />
?&gt;<br />
<br />
EOD is just an example stop/start name.<br />
<br />
This works too:<br />
<br />
&lt;?php&nbsp; // Html safe containers<br />
<br />
&nbsp; $myOutput = &lt;&lt;&lt;MYHTMLSAFEOUTPUT<br />
&lt;?xml version="1.0"?&gt;<br />
&lt;html&gt;<br />
&nbsp; &lt;title&gt;PHP Example&lt;/title&gt;<br />
&nbsp; &lt;body&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;p&gt;...all sorts goes here...&lt;/p&gt;<br />
&nbsp; &lt;/body&gt;<br />
&lt;/html&gt;<br />
MYHTMLSAFEOUTPUT;<br />
<br />
echo $myOutput;<br />
<br />
?&gt;<br />
<br />
Only disadvantage of using this is that all the code highlighting programs I've seen never get it right, making your code look eronous in the majority of viewers.<br />
<br />
Another alternative is to keep the XML / HTML in a separate include file and read in when needed. I don't know how efficient/inefficient this is for small amounts of text.<br />
<br />
xmlheader.txt:<br />
&lt;?xml version="1.0"?&gt;<br />
<br />
mypage.php:<br />
&lt;?php<br />
&nbsp; include("xmlheader.txt");<br />
?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="19982"></a>
 <div class="note">
  <strong class='user'>dave at [nospam] dot netready dot biz</strong>
  <a href="#19982" class="date">18-Mar-2002 04:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A little "feature" of PHP I've discovered is that the <span class="default">&lt;?PHP token requires a space after it whereas after the </span><span class="keyword">&lt;? and &lt;% </span><span class="default">tokens a space is optional</span><span class="keyword">.<br />
<br />
</span><span class="default">The error message you get </span><span class="keyword">if </span><span class="default">you miss the space is not too helpful so be warned</span><span class="keyword">!<br />
<br />
(</span><span class="default">These examples only give a warning with error_reporting</span><span class="keyword">(</span><span class="default">E_ALL</span><span class="keyword">) )<br />
<br />
&lt;?</span><span class="default">PHP</span><span class="comment">/*&lt;Some HTML&gt;*/</span><span class="default">?&gt;</span> fails...<br />
&lt;?/*&lt;Some HTML&gt;*/?&gt; works...</span>
</code></div>
  </div>
 </div>
 <a name="17505"></a>
 <div class="note">
  <strong class='user'>mrtidy at mail dot com</strong>
  <a href="#17505" class="date">12-Dec-2001 12:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
[Ed Note:<br />
This is because of short_tags, &lt;?xml turns php parsing on, because of the &lt;?.<br />
--irc-html@php.net]<br />
<br />
I am moving my site to XHTML and I ran into trouble with the &lt;?xml ?&gt; interfering with the <span class="default">&lt;?php ?&gt;</span> method of escaping for HTML.&nbsp; A quick check of the mailing list confirmed that the current preferred method to cleanly output the &lt;?xml ?&gt; line is to echo it:&lt;br&gt;<br />
<span class="default">&lt;?php </span><span class="keyword">echo(</span><span class="string">"&lt;?xml version=\"1.0\" encoding=\"UTF-8\"?&gt;\n"</span><span class="keyword">); </span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.basic-syntax&amp;redirect=http://www.php.net/manual/en/language.basic-syntax.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.basic-syntax&amp;redirect=http://www.php.net/manual/en/language.basic-syntax.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.basic-syntax.php">show source</a> |
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