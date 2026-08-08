<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Strings - Manual</title>
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
 <link rel="prev" href="language.types.float.php" />
 <link rel="next" href="language.types.array.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/types.string" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.types.string.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{GRSENCRS}" />
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
 <li class="active"><a href="language.types.string.php">Strings</a></li>
 <li><a href="language.types.array.php">Arrays</a></li>
 <li><a href="language.types.object.php">Objects</a></li>
 <li><a href="language.types.resource.php">Resources</a></li>
 <li><a href="language.types.null.php">NULL</a></li>
 <li><a href="language.types.callable.php">Callbacks</a></li>
 <li><a href="language.pseudo-types.php">Pseudo-types and variables used in this documentation</a></li>
 <li><a href="language.types.type-juggling.php">Type Juggling</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.types.array.php">Arrays<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.float.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Floating point numbers</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.string.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.types.string.php">Brazilian Portuguese</option>
    <option value="zh/language.types.string.php">Chinese (Simplified)</option>
    <option value="fr/language.types.string.php">French</option>
    <option value="de/language.types.string.php">German</option>
    <option value="ja/language.types.string.php">Japanese</option>
    <option value="pl/language.types.string.php">Polish</option>
    <option value="ro/language.types.string.php">Romanian</option>
    <option value="ru/language.types.string.php">Russian</option>
    <option value="fa/language.types.string.php">Persian</option>
    <option value="es/language.types.string.php">Spanish</option>
    <option value="tr/language.types.string.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.types.string" class="sect1">
 <h2 class="title">Strings</h2>

 
 <p class="para">
  A <span class="type"><a href="language.types.string.php" class="type string">string</a></span> is series of characters, where a character is
  the same as a byte. This means that PHP only supports a 256-character set,
  and hence does not offer native Unicode support. See
  <a href="language.types.string.php#language.types.string.details" class="link">details of the string
  type</a>.
 </p>

 <blockquote class="note"><p><strong class="note">Note</strong>: 
  <span class="simpara">
   <span class="type"><a href="language.types.string.php" class="type string">string</a></span> can be as large as 2GB.
  </span>
 </p></blockquote>

 <div class="sect2" id="language.types.string.syntax">
  <h3 class="title">Syntax</h3>

  <p class="para">
   A <span class="type"><a href="language.types.string.php" class="type string">string</a></span> literal can be specified in four different ways:
  </p>

  <ul class="itemizedlist">
   <li class="listitem">
    <span class="simpara">
     <a href="language.types.string.php#language.types.string.syntax.single" class="link">single quoted</a>
    </span>
   </li>
   <li class="listitem">
    <span class="simpara">
     <a href="language.types.string.php#language.types.string.syntax.double" class="link">double quoted</a>
    </span>
   </li>
   <li class="listitem">
    <span class="simpara">
     <a href="language.types.string.php#language.types.string.syntax.heredoc" class="link">heredoc syntax</a>
    </span>
   </li>
   <li class="listitem">
    <span class="simpara">
     <a href="language.types.string.php#language.types.string.syntax.nowdoc" class="link">nowdoc syntax</a>
     (since PHP 5.3.0)
    </span>
   </li>
  </ul>

  <div class="sect3" id="language.types.string.syntax.single">
   <h4 class="title">Single quoted</h4>

   <p class="para">
    The simplest way to specify a <span class="type"><a href="language.types.string.php" class="type string">string</a></span> is to enclose it in single
    quotes (the character <em>&#039;</em>).
   </p>

   <p class="para">
    To specify a literal single quote, escape it with a backslash
    (<em>\</em>). To specify a literal backslash, double it
    (<em>\\</em>). All other instances of backslash will be treated
    as a literal backslash: this means that the other escape sequences you
    might be used to, such as <em>\r</em> or <em>\n</em>,
    will be output literally as specified rather than having any special
    meaning.
   </p>

   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <span class="simpara">
     Unlike the <a href="language.types.string.php#language.types.string.syntax.double" class="link">double-quoted</a>
     and <a href="language.types.string.php#language.types.string.syntax.heredoc" class="link">heredoc</a> syntaxes,
     <a href="language.variables.php" class="link">variables</a> and escape sequences
     for special characters will <em class="emphasis">not</em> be expanded when they
     occur in single quoted <span class="type"><a href="language.types.string.php" class="type string">string</a></span>s.
    </span>
   </p></blockquote>

   <div class="informalexample">
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'this&nbsp;is&nbsp;a&nbsp;simple&nbsp;string'</span><span style="color: #007700">;<br /><br />echo&nbsp;</span><span style="color: #DD0000">'You&nbsp;can&nbsp;also&nbsp;have&nbsp;embedded&nbsp;newlines&nbsp;in&nbsp;<br />strings&nbsp;this&nbsp;way&nbsp;as&nbsp;it&nbsp;is<br />okay&nbsp;to&nbsp;do'</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;Outputs:&nbsp;Arnold&nbsp;once&nbsp;said:&nbsp;"I'll&nbsp;be&nbsp;back"<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'Arnold&nbsp;once&nbsp;said:&nbsp;"I\'ll&nbsp;be&nbsp;back"'</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;Outputs:&nbsp;You&nbsp;deleted&nbsp;C:\*.*?<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'You&nbsp;deleted&nbsp;C:\\*.*?'</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;Outputs:&nbsp;You&nbsp;deleted&nbsp;C:\*.*?<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'You&nbsp;deleted&nbsp;C:\*.*?'</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;Outputs:&nbsp;This&nbsp;will&nbsp;not&nbsp;expand:&nbsp;\n&nbsp;a&nbsp;newline<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'This&nbsp;will&nbsp;not&nbsp;expand:&nbsp;\n&nbsp;a&nbsp;newline'</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;Outputs:&nbsp;Variables&nbsp;do&nbsp;not&nbsp;$expand&nbsp;$either<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'Variables&nbsp;do&nbsp;not&nbsp;$expand&nbsp;$either'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>

  </div>

  <div class="sect3" id="language.types.string.syntax.double">
   <h4 class="title">Double quoted</h4>

   <p class="para">
    If the <span class="type"><a href="language.types.string.php" class="type string">string</a></span> is enclosed in double-quotes (&quot;), PHP will
    interpret more escape sequences for special characters:
   </p>

   <table class="doctable table">
    <caption><strong>Escaped characters</strong></caption>

    
     <thead>
      <tr>
       <th>Sequence</th>
       <th>Meaning</th>
      </tr>

     </thead>


     <tbody class="tbody">
      <tr>
       <td><em>\n</em></td>
       <td>linefeed (LF or 0x0A (10) in ASCII)</td>
      </tr>

      <tr>
       <td><em>\r</em></td>
       <td>carriage return (CR or 0x0D (13) in ASCII)</td>
      </tr>

      <tr>
       <td><em>\t</em></td>
       <td>horizontal tab (HT or 0x09 (9) in ASCII)</td>
      </tr>

      <tr>
       <td><em>\v</em></td>
       <td>vertical tab (VT or 0x0B (11) in ASCII) (since PHP 5.2.5)</td>
      </tr>

      <tr>
       <td><em>\e</em></td>
       <td>escape (ESC or 0x1B (27) in ASCII) (since PHP 5.4.0)</td>
      </tr>

      <tr>
       <td><em>\f</em></td>
       <td>form feed (FF or 0x0C (12) in ASCII) (since PHP 5.2.5)</td>
      </tr>

      <tr>
       <td><em>\\</em></td>
       <td>backslash</td>
      </tr>

      <tr>
       <td><em>\$</em></td>
       <td>dollar sign</td>
      </tr>

      <tr>
       <td><em>\&quot;</em></td>
       <td>double-quote</td>
      </tr>

      <tr>
       <td><em>\[0-7]{1,3}</em></td>
       <td>
        the sequence of characters matching the regular expression is a
        character in octal notation
       </td>
      </tr>

      <tr>
       <td><em>\x[0-9A-Fa-f]{1,2}</em></td>
       <td>
        the sequence of characters matching the regular expression is a
        character in hexadecimal notation
       </td>
      </tr>

     </tbody>
    
   </table>


   <p class="para">
    As in single quoted <span class="type"><a href="language.types.string.php" class="type string">string</a></span>s, escaping any other character will
    result in the backslash being printed too. Before PHP 5.1.1, the backslash
    in <em>\{$var}</em> had not been printed.
   </p>

   <p class="para">
    The most important feature of double-quoted <span class="type"><a href="language.types.string.php" class="type string">string</a></span>s is the fact
    that variable names will be expanded. See
    <a href="language.types.string.php#language.types.string.parsing" class="link">string parsing</a> for
    details.
   </p>
  </div>
  
  <div class="sect3" id="language.types.string.syntax.heredoc">
   <h4 class="title">Heredoc</h4>

   <p class="simpara">
    A third way to delimit <span class="type"><a href="language.types.string.php" class="type string">string</a></span>s is the heredoc syntax:
    <em>&lt;&lt;&lt;</em>. After this operator, an identifier is
    provided, then a newline. The <span class="type"><a href="language.types.string.php" class="type string">string</a></span> itself follows, and then
    the same identifier again to close the quotation. 
   </p>

   <p class="simpara">
    The closing identifier <em class="emphasis">must</em> begin in the first column
    of the line. Also, the identifier must follow the same naming rules as any
    other label in PHP: it must contain only alphanumeric characters and
    underscores, and must start with a non-digit character or underscore.
   </p>
   
   <div class="warning"><strong class="warning">Warning</strong>
    <p class="simpara">
     It is very important to note that the line with the closing identifier must
     contain no other characters, except <em class="emphasis">possibly</em> a
     semicolon (<em>;</em>). That means especially that the identifier
     <em class="emphasis">may not be indented</em>, and there may not be any spaces
     or tabs before or after the semicolon. It&#039;s also important to realize that
     the first character before the closing identifier must be a newline as
     defined by the local operating system. This is <em>\n</em> on
     UNIX systems, including Mac OS X. The closing delimiter (possibly followed
     by a semicolon) must also be followed by a newline.
    </p>

    <p class="simpara">
     If this rule is broken and the closing identifier is not &quot;clean&quot;, it will
     not be considered a closing identifier, and PHP will continue looking for
     one. If a proper closing identifier is not found before the end of the
     current file, a parse error will result at the last line.
    </p>

    <p class="para">
     Heredocs can not be used for initializing class properties. Since PHP 5.3,
     this limitation is valid only for heredocs containing variables.
    </p>
    
    <div class="example" id="example-71">
     <p><strong>Example #1 Invalid example</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;&lt;&lt;&lt;EOT<br /></span><span style="color: #DD0000">bar<br />&nbsp;&nbsp;&nbsp;&nbsp;EOT;<br />}<br />?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </div>

   <p class="para">
    Heredoc text behaves just like a double-quoted <span class="type"><a href="language.types.string.php" class="type string">string</a></span>, without
    the double quotes. This means that quotes in a heredoc do not need to be
    escaped, but the escape codes listed above can still be used. Variables are
    expanded, but the same care must be taken when expressing complex variables
    inside a heredoc as with <span class="type"><a href="language.types.string.php" class="type string">string</a></span>s.
   </p>

   <div class="example" id="example-72"> 
    <p><strong>Example #2 Heredoc string quoting example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$str&nbsp;</span><span style="color: #007700">=&nbsp;&lt;&lt;&lt;EOD<br /></span><span style="color: #DD0000">Example&nbsp;of&nbsp;string<br />spanning&nbsp;multiple&nbsp;lines<br />using&nbsp;heredoc&nbsp;syntax.<br /></span><span style="color: #007700">EOD;<br /><br /></span><span style="color: #FF8000">/*&nbsp;More&nbsp;complex&nbsp;example,&nbsp;with&nbsp;variables.&nbsp;*/<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">foo<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;var&nbsp;</span><span style="color: #0000BB">$foo</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;var&nbsp;</span><span style="color: #0000BB">$bar</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Foo'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">bar&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #DD0000">'Bar1'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'Bar2'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'Bar3'</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$name&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'MyName'</span><span style="color: #007700">;<br /><br />echo&nbsp;&lt;&lt;&lt;EOT<br /></span><span style="color: #DD0000">My&nbsp;name&nbsp;is&nbsp;"</span><span style="color: #0000BB">$name</span><span style="color: #DD0000">".&nbsp;I&nbsp;am&nbsp;printing&nbsp;some&nbsp;</span><span style="color: #0000BB">$foo</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo</span><span style="color: #DD0000">.<br />Now,&nbsp;I&nbsp;am&nbsp;printing&nbsp;some&nbsp;</span><span style="color: #007700">{</span><span style="color: #0000BB">$foo</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">bar</span><span style="color: #007700">[</span><span style="color: #0000BB">1</span><span style="color: #007700">]}</span><span style="color: #DD0000">.<br />This&nbsp;should&nbsp;print&nbsp;a&nbsp;capital&nbsp;'A':&nbsp;\x41<br /></span><span style="color: #007700">EOT;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
My name is &quot;MyName&quot;. I am printing some Foo.
Now, I am printing some Bar2.
This should print a capital &#039;A&#039;: A</pre></div>
    </div>
   </div>

   <p class="para">
    It is also possible to use the Heredoc syntax to pass data to function 
    arguments:
   </p>

   <div class="example" id="example-73"> 
    <p><strong>Example #3 Heredoc in arguments example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />var_dump</span><span style="color: #007700">(array(&lt;&lt;&lt;EOD<br /></span><span style="color: #DD0000">foobar!<br /></span><span style="color: #007700">EOD<br />));<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>

   <p class="para">
    As of PHP 5.3.0, it&#039;s possible to initialize static variables and class 
    properties/constants using the Heredoc syntax:
   </p>

   <div class="example" id="example-74"> 
    <p><strong>Example #4 Using Heredoc to initialize static values</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">//&nbsp;Static&nbsp;variables<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">()<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;static&nbsp;</span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;&lt;&lt;&lt;LABEL<br /></span><span style="color: #DD0000">Nothing&nbsp;in&nbsp;here...<br /></span><span style="color: #007700">LABEL;<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;Class&nbsp;properties/constants<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">foo<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;</span><span style="color: #0000BB">BAR&nbsp;</span><span style="color: #007700">=&nbsp;&lt;&lt;&lt;FOOBAR<br /></span><span style="color: #DD0000">Constant&nbsp;example<br /></span><span style="color: #007700">FOOBAR;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$baz&nbsp;</span><span style="color: #007700">=&nbsp;&lt;&lt;&lt;FOOBAR<br /></span><span style="color: #DD0000">Property&nbsp;example<br /></span><span style="color: #007700">FOOBAR;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>

   <p class="para">
    Starting with PHP 5.3.0, the opening Heredoc identifier may optionally be 
    enclosed in double quotes:
   </p>

   <div class="example" id="example-75"> 
    <p><strong>Example #5 Using double quotes in Heredoc</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">echo&nbsp;&lt;&lt;&lt;"FOOBAR"<br /></span><span style="color: #DD0000">Hello&nbsp;World!<br /></span><span style="color: #007700">FOOBAR;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>

  </div>
  
  <div class="sect3" id="language.types.string.syntax.nowdoc">
   <h4 class="title">Nowdoc</h4>
   
   <p class="para">
    Nowdocs are to single-quoted strings what heredocs are to double-quoted
    strings. A nowdoc is specified similarly to a heredoc, but <em class="emphasis">no
    parsing is done</em> inside a nowdoc. The construct is ideal for
    embedding PHP code or other large blocks of text without the need for
    escaping. It shares some features in common with the SGML
    <em>&lt;![CDATA[ ]]&gt;</em> construct, in that it declares a
    block of text which is not for parsing.
   </p>
   
   <p class="para">
    A nowdoc is identified with the same <em>&lt;&lt;&lt;</em>
    sequence used for heredocs, but the identifier which follows is enclosed in
    single quotes, e.g. <em>&lt;&lt;&lt;&#039;EOT&#039;</em>. All the rules for
    heredoc identifiers also apply to nowdoc identifiers, especially those
    regarding the appearance of the closing identifier.
   </p>
   
   <div class="example" id="example-76">
    <p><strong>Example #6 Nowdoc string quoting example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$str&nbsp;</span><span style="color: #007700">=&nbsp;&lt;&lt;&lt;'EOD'<br /></span><span style="color: #DD0000">Example&nbsp;of&nbsp;string<br />spanning&nbsp;multiple&nbsp;lines<br />using&nbsp;nowdoc&nbsp;syntax.<br /></span><span style="color: #007700">EOD;<br /><br /></span><span style="color: #FF8000">/*&nbsp;More&nbsp;complex&nbsp;example,&nbsp;with&nbsp;variables.&nbsp;*/<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">foo<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$foo</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$bar</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Foo'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">bar&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #DD0000">'Bar1'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'Bar2'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'Bar3'</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$name&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'MyName'</span><span style="color: #007700">;<br /><br />echo&nbsp;&lt;&lt;&lt;'EOT'<br /></span><span style="color: #DD0000">My&nbsp;name&nbsp;is&nbsp;"$name".&nbsp;I&nbsp;am&nbsp;printing&nbsp;some&nbsp;$foo-&gt;foo.<br />Now,&nbsp;I&nbsp;am&nbsp;printing&nbsp;some&nbsp;{$foo-&gt;bar[1]}.<br />This&nbsp;should&nbsp;not&nbsp;print&nbsp;a&nbsp;capital&nbsp;'A':&nbsp;\x41<br /></span><span style="color: #007700">EOT;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
My name is &quot;$name&quot;. I am printing some $foo-&gt;foo.
Now, I am printing some {$foo-&gt;bar[1]}.
This should not print a capital &#039;A&#039;: \x41</pre></div>
    </div>
   </div>
   
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     Unlike heredocs, nowdocs can be used in any static data context. The
     typical example is initializing class properties or constants:
    </p>
   </p></blockquote>
    
   <div class="example" id="example-77">
    <p><strong>Example #7 Static data example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;&lt;&lt;&lt;'EOT'<br /></span><span style="color: #DD0000">bar<br /></span><span style="color: #007700">EOT;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>

   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     Nowdoc support was added in PHP 5.3.0.
    </p>
   </p></blockquote>

  </div>

  <div class="sect3" id="language.types.string.parsing">
   <h4 class="title">Variable parsing</h4>

   <p class="simpara">
    When a <span class="type"><a href="language.types.string.php" class="type string">string</a></span> is specified in double quotes or with heredoc,
    <a href="language.variables.php" class="link">variables</a> are parsed within it. 
   </p>

   <p class="simpara">
    There are two types of syntax: a
    <a href="language.types.string.php#language.types.string.parsing.simple" class="link">simple</a> one and a
    <a href="language.types.string.php#language.types.string.parsing.complex" class="link">complex</a> one.
    The simple syntax is the most common and convenient. It provides a way to
    embed a variable, an <span class="type"><a href="language.types.array.php" class="type array">array</a></span> value, or an <span class="type"><a href="language.types.object.php" class="type object">object</a></span>
    property in a <span class="type"><a href="language.types.string.php" class="type string">string</a></span> with a minimum of effort.
   </p>

   <p class="simpara">
    The complex syntax can be recognised by the
    curly braces surrounding the expression.
   </p>

   <div class="sect4" id="language.types.string.parsing.simple">
    <h5 class="title">Simple syntax</h5>

    <p class="simpara">
     If a dollar sign (<em>$</em>) is encountered, the parser will
     greedily take as many tokens as possible to form a valid variable name.
     Enclose the variable name in curly braces to explicitly specify the end of
     the name.
    </p>

    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$juice&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"apple"</span><span style="color: #007700">;<br /><br />echo&nbsp;</span><span style="color: #DD0000">"He&nbsp;drank&nbsp;some&nbsp;</span><span style="color: #0000BB">$juice</span><span style="color: #DD0000">&nbsp;juice."</span><span style="color: #007700">.</span><span style="color: #0000BB">PHP_EOL</span><span style="color: #007700">;<br /></span><span style="color: #FF8000">//&nbsp;Invalid.&nbsp;"s"&nbsp;is&nbsp;a&nbsp;valid&nbsp;character&nbsp;for&nbsp;a&nbsp;variable&nbsp;name,&nbsp;but&nbsp;the&nbsp;variable&nbsp;is&nbsp;$juice.<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"He&nbsp;drank&nbsp;some&nbsp;juice&nbsp;made&nbsp;of&nbsp;</span><span style="color: #0000BB">$juices</span><span style="color: #DD0000">."</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

     <p class="para">The above example will output:</p>
     <div class="example-contents screen">
<div class="cdata"><pre>
He drank some apple juice.
He drank some juice made of .
</pre></div>
     </div>
    </div>

    <p class="simpara">
     Similarly, an <span class="type"><a href="language.types.array.php" class="type array">array</a></span> index or an <span class="type"><a href="language.types.object.php" class="type object">object</a></span> property
     can be parsed. With array indices, the closing square bracket
     (<em>]</em>) marks the end of the index. The same rules apply to
     object properties as to simple variables.
    </p>

    <div class="example" id="example-78"><p><strong>Example #8 Simple syntax example</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$juices&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #DD0000">"apple"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">"orange"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">"koolaid1"&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">"purple"</span><span style="color: #007700">);<br /><br />echo&nbsp;</span><span style="color: #DD0000">"He&nbsp;drank&nbsp;some&nbsp;</span><span style="color: #0000BB">$juices</span><span style="color: #007700">[</span><span style="color: #0000BB">0</span><span style="color: #007700">]</span><span style="color: #DD0000">&nbsp;juice."</span><span style="color: #007700">.</span><span style="color: #0000BB">PHP_EOL</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #DD0000">"He&nbsp;drank&nbsp;some&nbsp;</span><span style="color: #0000BB">$juices</span><span style="color: #007700">[</span><span style="color: #0000BB">1</span><span style="color: #007700">]</span><span style="color: #DD0000">&nbsp;juice."</span><span style="color: #007700">.</span><span style="color: #0000BB">PHP_EOL</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #DD0000">"He&nbsp;drank&nbsp;some&nbsp;juice&nbsp;made&nbsp;of&nbsp;</span><span style="color: #0000BB">$juice</span><span style="color: #007700">[</span><span style="color: #0000BB">0</span><span style="color: #007700">]</span><span style="color: #DD0000">s."</span><span style="color: #007700">.</span><span style="color: #0000BB">PHP_EOL</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;Won't&nbsp;work<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"He&nbsp;drank&nbsp;some&nbsp;</span><span style="color: #0000BB">$juices</span><span style="color: #007700">[</span><span style="color: #0000BB">koolaid1</span><span style="color: #007700">]</span><span style="color: #DD0000">&nbsp;juice."</span><span style="color: #007700">.</span><span style="color: #0000BB">PHP_EOL</span><span style="color: #007700">;<br /><br />class&nbsp;</span><span style="color: #0000BB">people&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$john&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"John&nbsp;Smith"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$jane&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"Jane&nbsp;Smith"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$robert&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"Robert&nbsp;Paulsen"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$smith&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"Smith"</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">$people&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">people</span><span style="color: #007700">();<br /><br />echo&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$people</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">john</span><span style="color: #DD0000">&nbsp;drank&nbsp;some&nbsp;</span><span style="color: #0000BB">$juices</span><span style="color: #007700">[</span><span style="color: #0000BB">0</span><span style="color: #007700">]</span><span style="color: #DD0000">&nbsp;juice."</span><span style="color: #007700">.</span><span style="color: #0000BB">PHP_EOL</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$people</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">john</span><span style="color: #DD0000">&nbsp;then&nbsp;said&nbsp;hello&nbsp;to&nbsp;</span><span style="color: #0000BB">$people</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">jane</span><span style="color: #DD0000">."</span><span style="color: #007700">.</span><span style="color: #0000BB">PHP_EOL</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$people</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">john</span><span style="color: #DD0000">'s&nbsp;wife&nbsp;greeted&nbsp;</span><span style="color: #0000BB">$people</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">robert</span><span style="color: #DD0000">."</span><span style="color: #007700">.</span><span style="color: #0000BB">PHP_EOL</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$people</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">robert</span><span style="color: #DD0000">&nbsp;greeted&nbsp;the&nbsp;two&nbsp;</span><span style="color: #0000BB">$people</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">smiths</span><span style="color: #DD0000">."</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;Won't&nbsp;work<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

     <div class="example-contents"><p>The above example will output:</p></div>
     <div class="example-contents screen">
<div class="cdata"><pre>
He drank some apple juice.
He drank some orange juice.
He drank some juice made of s.
He drank some purple juice.
John Smith drank some apple juice.
John Smith then said hello to Jane Smith.
John Smith&#039;s wife greeted Robert Paulsen.
Robert Paulsen greeted the two .
</pre></div>
     </div>
    </div>

    <p class="simpara">
     For anything more complex, you should use the complex syntax.
    </p>
   </div>

   <div class="sect4" id="language.types.string.parsing.complex">
    <h5 class="title">Complex (curly) syntax</h5>

    <p class="simpara">
     This isn&#039;t called complex because the syntax is complex, but because it
     allows for the use of complex expressions.
    </p>

    <p class="simpara">
     Any scalar variable, array element or object property with a
     <span class="type"><a href="language.types.string.php" class="type string">string</a></span> representation can be included via this syntax.
     Simply write the expression the same way as it would appear outside the
     <span class="type"><a href="language.types.string.php" class="type string">string</a></span>, and then wrap it in <em>{</em> and
     <em>}</em>. Since <em>{</em> can not be escaped, this
     syntax will only be recognised when the <em>$</em> immediately
     follows the <em>{</em>. Use <em>{\$</em> to get a
     literal <em>{$</em>. Some examples to make it clear:
    </p>

    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">//&nbsp;Show&nbsp;all&nbsp;errors<br /></span><span style="color: #0000BB">error_reporting</span><span style="color: #007700">(</span><span style="color: #0000BB">E_ALL</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">$great&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'fantastic'</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;Won't&nbsp;work,&nbsp;outputs:&nbsp;This&nbsp;is&nbsp;{&nbsp;fantastic}<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;is&nbsp;{&nbsp;</span><span style="color: #0000BB">$great</span><span style="color: #DD0000">}"</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;Works,&nbsp;outputs:&nbsp;This&nbsp;is&nbsp;fantastic<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;is&nbsp;</span><span style="color: #007700">{</span><span style="color: #0000BB">$great</span><span style="color: #007700">}</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;is&nbsp;</span><span style="color: #007700">${</span><span style="color: #0000BB">great</span><span style="color: #007700">}</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;Works<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;square&nbsp;is&nbsp;</span><span style="color: #007700">{</span><span style="color: #0000BB">$square</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">width</span><span style="color: #007700">}</span><span style="color: #DD0000">00&nbsp;centimeters&nbsp;broad."</span><span style="color: #007700">;&nbsp;<br /><br /><br /></span><span style="color: #FF8000">//&nbsp;Works,&nbsp;quoted&nbsp;keys&nbsp;only&nbsp;work&nbsp;using&nbsp;the&nbsp;curly&nbsp;brace&nbsp;syntax<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;works:&nbsp;</span><span style="color: #007700">{</span><span style="color: #0000BB">$arr</span><span style="color: #007700">[</span><span style="color: #DD0000">'key'</span><span style="color: #007700">]}</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br /><br /><br /></span><span style="color: #FF8000">//&nbsp;Works<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;works:&nbsp;</span><span style="color: #007700">{</span><span style="color: #0000BB">$arr</span><span style="color: #007700">[</span><span style="color: #0000BB">4</span><span style="color: #007700">][</span><span style="color: #0000BB">3</span><span style="color: #007700">]}</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;This&nbsp;is&nbsp;wrong&nbsp;for&nbsp;the&nbsp;same&nbsp;reason&nbsp;as&nbsp;$foo[bar]&nbsp;is&nbsp;wrong&nbsp;&nbsp;outside&nbsp;a&nbsp;string.<br />//&nbsp;In&nbsp;other&nbsp;words,&nbsp;it&nbsp;will&nbsp;still&nbsp;work,&nbsp;but&nbsp;only&nbsp;because&nbsp;PHP&nbsp;first&nbsp;looks&nbsp;for&nbsp;a<br />//&nbsp;constant&nbsp;named&nbsp;foo;&nbsp;an&nbsp;error&nbsp;of&nbsp;level&nbsp;E_NOTICE&nbsp;(undefined&nbsp;constant)&nbsp;will&nbsp;be<br />//&nbsp;thrown.<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;is&nbsp;wrong:&nbsp;</span><span style="color: #007700">{</span><span style="color: #0000BB">$arr</span><span style="color: #007700">[</span><span style="color: #0000BB">foo</span><span style="color: #007700">][</span><span style="color: #0000BB">3</span><span style="color: #007700">]}</span><span style="color: #DD0000">"</span><span style="color: #007700">;&nbsp;<br /><br /></span><span style="color: #FF8000">//&nbsp;Works.&nbsp;When&nbsp;using&nbsp;multi-dimensional&nbsp;arrays,&nbsp;always&nbsp;use&nbsp;braces&nbsp;around&nbsp;arrays<br />//&nbsp;when&nbsp;inside&nbsp;of&nbsp;strings<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;works:&nbsp;</span><span style="color: #007700">{</span><span style="color: #0000BB">$arr</span><span style="color: #007700">[</span><span style="color: #DD0000">'foo'</span><span style="color: #007700">][</span><span style="color: #0000BB">3</span><span style="color: #007700">]}</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;Works.<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;works:&nbsp;"&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">$arr</span><span style="color: #007700">[</span><span style="color: #DD0000">'foo'</span><span style="color: #007700">][</span><span style="color: #0000BB">3</span><span style="color: #007700">];<br /><br />echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;works&nbsp;too:&nbsp;</span><span style="color: #007700">{</span><span style="color: #0000BB">$obj</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">values</span><span style="color: #007700">[</span><span style="color: #0000BB">3</span><span style="color: #007700">]-&gt;</span><span style="color: #0000BB">name</span><span style="color: #007700">}</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br /><br />echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;is&nbsp;the&nbsp;value&nbsp;of&nbsp;the&nbsp;var&nbsp;named&nbsp;</span><span style="color: #0000BB">$name</span><span style="color: #DD0000">:&nbsp;</span><span style="color: #007700">{${</span><span style="color: #0000BB">$name</span><span style="color: #007700">}}</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br /><br />echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;is&nbsp;the&nbsp;value&nbsp;of&nbsp;the&nbsp;var&nbsp;named&nbsp;by&nbsp;the&nbsp;return&nbsp;value&nbsp;of&nbsp;getName():&nbsp;</span><span style="color: #007700">{${</span><span style="color: #0000BB">getName</span><span style="color: #007700">()}}</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br /><br />echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;is&nbsp;the&nbsp;value&nbsp;of&nbsp;the&nbsp;var&nbsp;named&nbsp;by&nbsp;the&nbsp;return&nbsp;value&nbsp;of&nbsp;\$object-&gt;getName():&nbsp;</span><span style="color: #007700">{${</span><span style="color: #0000BB">$object</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">getName</span><span style="color: #007700">()}}</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;Won't&nbsp;work,&nbsp;outputs:&nbsp;This&nbsp;is&nbsp;the&nbsp;return&nbsp;value&nbsp;of&nbsp;getName():&nbsp;{getName()}<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;is&nbsp;the&nbsp;return&nbsp;value&nbsp;of&nbsp;getName():&nbsp;{getName()}"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>

     </div>

    </div>

    <p class="para">
     It is also possible to access class properties using variables
     within strings using this syntax.
    </p>

   <div class="informalexample">
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;var&nbsp;</span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'I&nbsp;am&nbsp;bar.'</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'bar'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$baz&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #DD0000">'foo'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'bar'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'baz'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'quux'</span><span style="color: #007700">);<br />echo&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #007700">{</span><span style="color: #0000BB">$foo</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">$bar</span><span style="color: #007700">}</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #007700">{</span><span style="color: #0000BB">$foo</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">$baz</span><span style="color: #007700">[</span><span style="color: #0000BB">1</span><span style="color: #007700">]}</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   <p class="para">The above example will output:</p>
   <div class="example-contents screen">
<div class="cdata"><pre>
I am bar.
I am bar.
</pre></div>
   </div>
   </div>
    
    <blockquote class="note"><p><strong class="note">Note</strong>: 
     <p class="para">
      Functions, method calls, static class variables, and class
      constants inside <em>{$}</em> work since PHP
      5. However, the value accessed will be interpreted as the name
      of a variable in the scope in which the string is defined. Using
      single curly braces (<em>{}</em>) will not work for
      accessing the return values of functions or methods or the
      values of class constants or static class variables.
     </p>
    </p></blockquote>

    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">//&nbsp;Show&nbsp;all&nbsp;errors.<br /></span><span style="color: #0000BB">error_reporting</span><span style="color: #007700">(</span><span style="color: #0000BB">E_ALL</span><span style="color: #007700">);<br /><br />class&nbsp;</span><span style="color: #0000BB">beers&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;</span><span style="color: #0000BB">softdrink&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'rootbeer'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;static&nbsp;</span><span style="color: #0000BB">$ale&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'ipa'</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">$rootbeer&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'A&nbsp;&amp;&nbsp;W'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$ipa&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Alexander&nbsp;Keith\'s'</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;This&nbsp;works;&nbsp;outputs:&nbsp;I'd&nbsp;like&nbsp;an&nbsp;A&nbsp;&amp;&nbsp;W<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"I'd&nbsp;like&nbsp;an&nbsp;</span><span style="color: #007700">{${</span><span style="color: #0000BB">beers</span><span style="color: #007700">::</span><span style="color: #0000BB">softdrink</span><span style="color: #007700">}}</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;This&nbsp;works&nbsp;too;&nbsp;outputs:&nbsp;I'd&nbsp;like&nbsp;an&nbsp;Alexander&nbsp;Keith's<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"I'd&nbsp;like&nbsp;an&nbsp;</span><span style="color: #007700">{${</span><span style="color: #0000BB">beers</span><span style="color: #007700">::</span><span style="color: #0000BB">$ale</span><span style="color: #007700">}}</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>

   </div>
  </div>
  
  <div class="sect3" id="language.types.string.substr">
   <h4 class="title">String access and modification by character</h4>

   <p class="para">
    Characters within <span class="type"><a href="language.types.string.php" class="type string">string</a></span>s may be accessed and modified by
    specifying the zero-based offset of the desired character after the
    <span class="type"><a href="language.types.string.php" class="type string">string</a></span> using square <span class="type"><a href="language.types.array.php" class="type array">array</a></span> brackets, as in
    <var class="varname"><var class="varname">$str[42]</var></var>. Think of a <span class="type"><a href="language.types.string.php" class="type string">string</a></span> as an
    <span class="type"><a href="language.types.array.php" class="type array">array</a></span> of characters for this purpose. The functions
     <span class="function"><a href="function.substr.php" class="function">substr()</a></span> and  <span class="function"><a href="function.substr-replace.php" class="function">substr_replace()</a></span>
    can be used when you want to extract or replace more than 1 character.
   </p>

   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <span class="simpara">
     <span class="type"><a href="language.types.string.php" class="type String">String</a></span>s may also be accessed using braces, as in
     <var class="varname"><var class="varname">$str{42}</var></var>, for the same purpose.
    </span>
   </p></blockquote>

   <div class="warning"><strong class="warning">Warning</strong>
    <p class="simpara">
     Writing to an out of range offset pads the string with spaces.
     Non-integer types are converted to integer.
     Illegal offset type emits <strong><code>E_NOTICE</code></strong>.
     Negative offset emits <strong><code>E_NOTICE</code></strong> in write but reads empty string.
     Only the first character of an assigned string is used.
     Assigning empty string assigns NULL byte.
    </p>
   </div>

   <div class="example" id="example-79">
    <p><strong>Example #9 Some string examples</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">//&nbsp;Get&nbsp;the&nbsp;first&nbsp;character&nbsp;of&nbsp;a&nbsp;string<br /></span><span style="color: #0000BB">$str&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'This&nbsp;is&nbsp;a&nbsp;test.'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$first&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$str</span><span style="color: #007700">[</span><span style="color: #0000BB">0</span><span style="color: #007700">];<br /><br /></span><span style="color: #FF8000">//&nbsp;Get&nbsp;the&nbsp;third&nbsp;character&nbsp;of&nbsp;a&nbsp;string<br /></span><span style="color: #0000BB">$third&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$str</span><span style="color: #007700">[</span><span style="color: #0000BB">2</span><span style="color: #007700">];<br /><br /></span><span style="color: #FF8000">//&nbsp;Get&nbsp;the&nbsp;last&nbsp;character&nbsp;of&nbsp;a&nbsp;string.<br /></span><span style="color: #0000BB">$str&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'This&nbsp;is&nbsp;still&nbsp;a&nbsp;test.'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$last&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$str</span><span style="color: #007700">[</span><span style="color: #0000BB">strlen</span><span style="color: #007700">(</span><span style="color: #0000BB">$str</span><span style="color: #007700">)-</span><span style="color: #0000BB">1</span><span style="color: #007700">];&nbsp;<br /><br /></span><span style="color: #FF8000">//&nbsp;Modify&nbsp;the&nbsp;last&nbsp;character&nbsp;of&nbsp;a&nbsp;string<br /></span><span style="color: #0000BB">$str&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Look&nbsp;at&nbsp;the&nbsp;sea'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$str</span><span style="color: #007700">[</span><span style="color: #0000BB">strlen</span><span style="color: #007700">(</span><span style="color: #0000BB">$str</span><span style="color: #007700">)-</span><span style="color: #0000BB">1</span><span style="color: #007700">]&nbsp;=&nbsp;</span><span style="color: #DD0000">'e'</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>

   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     Accessing variables of other types (not including arrays or objects
     implementing the appropriate interfaces) using <em>[]</em> or
     <em>{}</em> silently returns <strong><code>NULL</code></strong>.
    </p>
   </p></blockquote>

  </div>
 </div>

 <div class="sect2" id="language.types.string.useful-funcs">
  <h3 class="title">Useful functions and operators</h3>

  <p class="para">
   <span class="type"><a href="language.types.string.php" class="type String">String</a></span>s may be concatenated using the &#039;.&#039; (dot) operator. Note
   that the &#039;+&#039; (addition) operator will <em class="emphasis">not</em> work for this.
   See <a href="language.operators.string.php" class="link">String operators</a> for
   more information.
  </p>

  <p class="para">
   There are a number of useful functions for <span class="type"><a href="language.types.string.php" class="type string">string</a></span> manipulation.
  </p>

  <p class="simpara">
   See the <a href="ref.strings.php" class="link">string functions section</a> for
   general functions, and the <a href="ref.regex.php" class="link">regular expression
   functions</a> or the <a href="ref.pcre.php" class="link">Perl-compatible regular
   expression functions</a> for advanced find &amp; replace functionality.
  </p>

  <p class="simpara">
   There are also <a href="ref.url.php" class="link">functions for URL strings</a>, and
   functions to encrypt/decrypt strings
   (<a href="ref.mcrypt.php" class="link">mcrypt</a> and
   <a href="ref.mhash.php" class="link">mhash</a>).
  </p>

  <p class="simpara">
   Finally, see also the <a href="ref.ctype.php" class="link">character type
   functions</a>.
  </p>
 </div>

 <div class="sect2" id="language.types.string.casting">
  <h3 class="title">Converting to string</h3>
  
  <p class="para">
   A value can be converted to a <span class="type"><a href="language.types.string.php" class="type string">string</a></span> using the
   <em>(string)</em> cast or the  <span class="function"><a href="function.strval.php" class="function">strval()</a></span> function.
   <span class="type"><a href="language.types.string.php" class="type String">String</a></span> conversion is automatically done in the scope of an
   expression where a <span class="type"><a href="language.types.string.php" class="type string">string</a></span> is needed. This happens when using the
    <span class="function"><a href="function.echo.php" class="function">echo</a></span> or  <span class="function"><a href="function.print.php" class="function">print</a></span> functions, or when a
   variable is compared to a <span class="type"><a href="language.types.string.php" class="type string">string</a></span>. The sections on
   <a href="language.types.php" class="link">Types</a> and
   <a href="language.types.type-juggling.php" class="link">Type Juggling</a> will make
   the following clearer. See also the  <span class="function"><a href="function.settype.php" class="function">settype()</a></span> function.
  </p>
  
  <p class="para">
   A <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span> <strong><code>TRUE</code></strong> value is converted to the <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
   <em>&quot;1&quot;</em>. <span class="type"><a href="language.types.boolean.php" class="type Boolean">Boolean</a></span> <strong><code>FALSE</code></strong> is converted to
   <em>&quot;&quot;</em> (the empty string). This allows conversion back and
   forth between <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span> and <span class="type"><a href="language.types.string.php" class="type string">string</a></span> values.
  </p>

  <p class="para"> 
   An <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> or <span class="type"><a href="language.types.float.php" class="type float">float</a></span> is converted to a
   <span class="type"><a href="language.types.string.php" class="type string">string</a></span> representing the number textually (including the
   exponent part for <span class="type"><a href="language.types.float.php" class="type float">float</a></span>s). Floating point numbers can be
   converted using exponential notation (<em>4.1E+6</em>).
  </p>

  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <p class="para">
    The decimal point character is defined in the script&#039;s locale (category
    LC_NUMERIC). See the  <span class="function"><a href="function.setlocale.php" class="function">setlocale()</a></span> function.
   </p>
  </p></blockquote>

  <p class="para">
   <span class="type"><a href="language.types.array.php" class="type Array">Array</a></span>s are always converted to the <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
   <em>&quot;Array&quot;</em>; because of this,  <span class="function"><a href="function.echo.php" class="function">echo</a></span> and
    <span class="function"><a href="function.print.php" class="function">print</a></span> can not by themselves show the contents of an
   <span class="type"><a href="language.types.array.php" class="type array">array</a></span>. To view a single element, use a construction such as
   <em>echo $arr[&#039;foo&#039;]</em>. See below for tips on viewing the entire
   contents.
  </p>

  <p class="para">
   <span class="type"><a href="language.types.object.php" class="type Object">Object</a></span>s in PHP 4 are always converted to the <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
   <em>&quot;Object&quot;</em>. To print the values of object properties for
   debugging reasons, read the paragraphs below. To get an object&#039;s class name,
   use the  <span class="function"><a href="function.get-class.php" class="function">get_class()</a></span> function. As of PHP 5, the
   <a href="language.oop5.magic.php" class="link">__toString</a> method is used when
   applicable.
  </p>

  <p class="para">
   <span class="type"><a href="language.types.resource.php" class="type Resource">Resource</a></span>s are always converted to <span class="type"><a href="language.types.string.php" class="type string">string</a></span>s with the
   structure <em>&quot;Resource id #1&quot;</em>, where <em>1</em> is
   the unique number assigned to the <span class="type"><a href="language.types.resource.php" class="type resource">resource</a></span> by PHP at runtime. Do
   not rely upon this structure; it is subject to change. To get a
   <span class="type"><a href="language.types.resource.php" class="type resource">resource</a></span>&#039;s type, use the
    <span class="function"><a href="function.get-resource-type.php" class="function">get_resource_type()</a></span> function.
  </p>

  <p class="para">
   <strong><code>NULL</code></strong> is always converted to an empty string.
  </p>
  
  <p class="para">
   As stated above, directly converting an <span class="type"><a href="language.types.array.php" class="type array">array</a></span>,
   <span class="type"><a href="language.types.object.php" class="type object">object</a></span>, or <span class="type"><a href="language.types.resource.php" class="type resource">resource</a></span> to a <span class="type"><a href="language.types.string.php" class="type string">string</a></span> does
   not provide any useful information about the value beyond its type. See the
   functions  <span class="function"><a href="function.print-r.php" class="function">print_r()</a></span> and  <span class="function"><a href="function.var-dump.php" class="function">var_dump()</a></span> for
   more effective means of inspecting the contents of these types.
  </p>
  
  <p class="para">
   Most PHP values can also be converted to <span class="type"><a href="language.types.string.php" class="type string">string</a></span>s for permanent
   storage. This method is called serialization, and is performed by the
    <span class="function"><a href="function.serialize.php" class="function">serialize()</a></span> function. If the PHP engine was built with
   <a href="ref.wddx.php" class="link">WDDX</a> support, PHP values can also be
   serialized as well-formed XML text.
  </p>

 </div>

 <div class="sect2" id="language.types.string.conversion">
  <h3 class="title">String conversion to numbers</h3>

  <p class="simpara">
   When a <span class="type"><a href="language.types.string.php" class="type string">string</a></span> is evaluated in a numeric context, the resulting
   value and type are determined as follows.
  </p>

  <p class="simpara">
   If the <span class="type"><a href="language.types.string.php" class="type string">string</a></span> does not contain any of the characters &#039;.&#039;, &#039;e&#039;,
   or &#039;E&#039; and the numeric value fits into integer type limits (as defined by
   <strong><code>PHP_INT_MAX</code></strong>), the <span class="type"><a href="language.types.string.php" class="type string">string</a></span> will be evaluated
   as an <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span>. In all other cases it will be evaluated as a
   <span class="type"><a href="language.types.float.php" class="type float">float</a></span>.
  </p>

  <p class="para">
   The value is given by the initial portion of the <span class="type"><a href="language.types.string.php" class="type string">string</a></span>. If the
   <span class="type"><a href="language.types.string.php" class="type string">string</a></span> starts with valid numeric data, this will be the value
   used. Otherwise, the value will be 0 (zero). Valid numeric data is an
   optional sign, followed by one or more digits (optionally containing a
   decimal point), followed by an optional exponent. The exponent is an &#039;e&#039; or
   &#039;E&#039; followed by one or more digits.
  </p>

  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #DD0000">"10.5"</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;$foo&nbsp;is&nbsp;float&nbsp;(11.5)<br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #DD0000">"-1.3e3"</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;$foo&nbsp;is&nbsp;float&nbsp;(-1299)<br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #DD0000">"bob-1.3e3"</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;$foo&nbsp;is&nbsp;integer&nbsp;(1)<br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #DD0000">"bob3"</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;$foo&nbsp;is&nbsp;integer&nbsp;(1)<br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #DD0000">"10&nbsp;Small&nbsp;Pigs"</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;$foo&nbsp;is&nbsp;integer&nbsp;(11)<br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">4&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #DD0000">"10.2&nbsp;Little&nbsp;Piggies"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;$foo&nbsp;is&nbsp;float&nbsp;(14.2)<br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"10.0&nbsp;pigs&nbsp;"&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;$foo&nbsp;is&nbsp;float&nbsp;(11)<br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"10.0&nbsp;pigs&nbsp;"&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">1.0</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;$foo&nbsp;is&nbsp;float&nbsp;(11)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>

  <p class="simpara">
   For more information on this conversion, see the Unix manual page for
   strtod(3).
  </p>

  <p class="para">
   To test any of the examples in this section, cut and paste the examples and
   insert the following line to see what&#039;s going on:
  </p>

  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"\$foo==</span><span style="color: #0000BB">$foo</span><span style="color: #DD0000">;&nbsp;type&nbsp;is&nbsp;"&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">gettype&nbsp;</span><span style="color: #007700">(</span><span style="color: #0000BB">$foo</span><span style="color: #007700">)&nbsp;.&nbsp;</span><span style="color: #DD0000">"&lt;br&nbsp;/&gt;\n"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>

  <p class="para">
   Do not expect to get the code of one character by converting it to integer,
   as is done in C. Use the  <span class="function"><a href="function.ord.php" class="function">ord()</a></span> and
    <span class="function"><a href="function.chr.php" class="function">chr()</a></span> functions to convert between ASCII codes and
   characters.
  </p>

 </div>

 <div class="sect2" id="language.types.string.details">
  
  <h3 class="title">Details of the String Type</h3>
  
  <p class="para">
   The <span class="type"><a href="language.types.string.php" class="type string">string</a></span> in PHP is implemented as an array of bytes and an
   integer indicating the length of the buffer. It has no information about how
   those bytes translate to characters, leaving that task to the programmer.
   There are no limitations on the values the string can be composed of; in
   particular, bytes with value <em>0</em> (“NUL bytes”) are allowed
   anywhere in the string (however, a few functions, said in this manual not to
   be “binary safe”, may hand off the strings to libraries that ignore data
   after a NUL byte.)
  </p>
  <p class="para">
   This nature of the string type explains why there is no separate “byte” type
   in PHP – strings take this role. Functions that return no textual data – for
   instance, arbitrary data read from a network socket – will still return
   strings.
  </p>
  <p class="para">
   Given that PHP does not dictate a specific encoding for strings, one might
   wonder how string literals are encoded. For instance, is the string
   <em>&quot;á&quot;</em> equivalent to <em>&quot;\xE1&quot;</em> (ISO-8859-1),
   <em>&quot;\xC3\xA1&quot;</em> (UTF-8, C form),
   <em>&quot;\x61\xCC\x81&quot;</em> (UTF-8, D form) or any other possible
   representation? The answer is that string will be encoded in whatever fashion
   it is encoded in the script file. Thus, if the script is written in
   ISO-8859-1, the string will be encoded in ISO-8859-1 and so on. However,
   this does not apply if Zend Multibyte is enabled; in that case, the script
   may be written in an arbitrary encoding (which is explicity declared or is
   detected) and then converted to a certain internal encoding, which is then
   the encoding that will be used for the string literals.
   Note that there are some constraints on the encoding of the script (or on the
   internal encoding, should Zend Multibyte be enabled) – this almost always
   means that this encoding should be a compatible superset of ASCII, such as
   UTF-8 or ISO-8859-1. Note, however, that state-dependent encodings where
   the same byte values can be used in initial and non-initial shift states
   may be problematic.
  </p>
  <p class="para">
   Of course, in order to be useful, functions that operate on text may have to
   make some assumptions about how the string is encoded. Unfortunately, there
   is much variation on this matter throughout PHP’s functions:
  </p>
  <ul class="itemizedlist">
   <li class="listitem">
    <span class="simpara">
     Some functions assume that the string is encoded in some (any) single-byte
     encoding, but they do not need to interpret those bytes as specific
     characters. This is case of, for instance,  <span class="function"><a href="function.substr.php" class="function">substr()</a></span>, 
      <span class="function"><a href="function.strpos.php" class="function">strpos()</a></span>,  <span class="function"><a href="function.strlen.php" class="function">strlen()</a></span> or
      <span class="function"><a href="function.strcmp.php" class="function">strcmp()</a></span>. Another way to think of these functions is
     that operate on memory buffers, i.e., they work with bytes and byte
     offsets.
    </span>
   </li>
   <li class="listitem">
    <span class="simpara">
     Other functions are passed the encoding of the string, possibly they also
     assume a default if no such information is given. This is the case of
      <span class="function"><a href="function.htmlentities.php" class="function">htmlentities()</a></span> and the majority of the
     functions in the <a href="book.mbstring.php" class="link">mbstring</a> extension.
    </span>
   </li>
   <li class="listitem">
    <span class="simpara">
     Others use the current locale (see  <span class="function"><a href="function.setlocale.php" class="function">setlocale()</a></span>), but
     operate byte-by-byte. This is the case of  <span class="function"><a href="function.strcasecmp.php" class="function">strcasecmp()</a></span>,
      <span class="function"><a href="function.strtoupper.php" class="function">strtoupper()</a></span> and  <span class="function"><a href="function.ucfirst.php" class="function">ucfirst()</a></span>.
     This means they can be used only with single-byte encodings, as long as
     the encoding is matched by the locale. For instance
     <em>strtoupper(&quot;á&quot;)</em> may return <em>&quot;Á&quot;</em> if the
     locale is correctly set and <em>á</em> is encoded with a single
     byte. If it is encoded in UTF-8, the correct result will not be returned
     and the resulting string may or may not be returned corrupted, depending
     on the current locale.
    </span>
   </li>
   <li class="listitem">
    <span class="simpara">
     Finally, they may just assume the string is using a specific encoding,
     usually UTF-8. This is the case of most functions in the
     <a href="book.intl.php" class="link">intl</a> extension and in the
     <a href="book.pcre.php" class="link">PCRE</a> extension
     (in the last case, only when the <em>u</em> modifier is used).
     Although this is due to their special purpose, the function
      <span class="function"><a href="function.utf8-decode.php" class="function">utf8_decode()</a></span> assumes a UTF-8 encoding and the
     function  <span class="function"><a href="function.utf8-encode.php" class="function">utf8_encode()</a></span> assumes an ISO-8859-1 encoding.
    </span>
   </li>
  </ul>

  <p class="para">
   Ultimately, this means writing correct programs using Unicode depends on
   carefully avoiding functions that will not work and that most likely will
   corrupt the data and using instead the functions that do behave correctly,
   generally from the <a href="book.intl.php" class="link">intl</a> and
   <a href="book.mbstring.php" class="link">mbstring</a> extensions.
   However, using functions that can handle Unicode encodings is just the
   beginning. No matter the functions the language provides, it is essential to
   know the Unicode specification. For instance, a program that assumes there is
   only uppercase and lowercase is making a wrong assumption.
  </p>
 </div>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.types.array.php">Arrays<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.float.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Floating point numbers</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.string.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.types.string&amp;redirect=@w{GRSENCRS}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types.string&amp;redirect=@w{GRSENCRS}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Strings</strong>
 </div><div id="allnotes">
 <a name="108984"></a>
 <div class="note">
  <strong class='user'>Denis R.</strong>
  <a href="#108984" class="date">10-Jun-2012 11:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Hi.<br />
<br />
I noticed that the documentation does not mention that when you have an XML element which contains a dash (-) in its name can only be accessed using the bracelets notation.<br />
For example:<br />
&lt;xml version="1"&gt;<br />
&lt;root&gt;<br />
&nbsp;&nbsp; &lt;element-one&gt;value4element-one&lt;/element-one&gt;<br />
&lt;/root&gt;<br />
<br />
to access the above 'element-one' using SimpleXML you need to use the following:<br />
<br />
$simpleXMLObj-&gt;root-&gt;{'element-one'}<br />
<br />
to retrieve the value.<br />
<br />
Hope this helps,<br />
Denis R.</span>
</code></div>
  </div>
 </div>
 <a name="108144"></a>
 <div class="note">
  <strong class='user'>m021 at springtimesoftware dot com</strong>
  <a href="#108144" class="date">01-Apr-2012 02:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Heredoc literals delete any trailing space (tabs and blanks) on each line. This is unexpected, since quoted strings do not do this. This is probably done for historical reasons, so would not be considered a bug.</span>
</code></div>
  </div>
 </div>
 <a name="107138"></a>
 <div class="note">
  <strong class='user'>gtisza at gmail dot com</strong>
  <a href="#107138" class="date">10-Jan-2012 06:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The documentation does not mention, but a closing semicolon at the end of the heredoc is actually interpreted as a real semicolon, and as such, sometimes leads to syntax errors.<br />
<br />
This works:<br />
<br />
<span class="default">&lt;?php<br />
$foo </span><span class="keyword">= &lt;&lt;&lt;END<br />
</span><span class="default">abcd<br />
</span><span class="keyword">END;<br />
</span><span class="default">?&gt;<br />
</span><br />
This does not:<br />
<br />
<span class="default">&lt;?php<br />
foo</span><span class="keyword">(&lt;&lt;&lt;END<br />
</span><span class="default">abcd<br />
</span><span class="keyword">END;<br />
);<br />
</span><span class="comment">// syntax error, unexpected ';'<br />
</span><span class="default">?&gt;<br />
</span><br />
Without semicolon, it works fine:<br />
<br />
<span class="default">&lt;?php<br />
foo</span><span class="keyword">(&lt;&lt;&lt;END<br />
</span><span class="default">abcd<br />
</span><span class="keyword">END<br />
);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="105835"></a>
 <div class="note">
  <strong class='user'>MarkSG</strong>
  <a href="#105835" class="date">19-Sep-2011 01:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Actually, HEREDOC works precisely as documented, with nothing hidden at all. It's just that it's one of those things which isn't entirely intuitive to grasp.<br />
<br />
A HEREDOC block has to start with the delimiter followed by a newline, and end with a newline followed by the delimiter. That means that the leading and trailing newlines are part of the syntax, not a part of the block. So this<br />
<br />
<span class="default">&lt;?php<br />
$foo </span><span class="keyword">= &lt;&lt;&lt;EOF<br />
</span><span class="default">this<br />
is<br />
a<br />
block<br />
</span><span class="keyword">EOF;<br />
</span><span class="default">?&gt;<br />
</span><br />
is equivalent to this<br />
<br />
<span class="default">&lt;?php<br />
$foo </span><span class="keyword">= <br />
</span><span class="string">"this<br />
is<br />
a<br />
block"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
or this<br />
<br />
<span class="default">&lt;?php<br />
$foo </span><span class="keyword">= </span><span class="string">"this\nis\na\nblock"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
but not this:<br />
<br />
<span class="default">&lt;?php<br />
$foo </span><span class="keyword">= </span><span class="string">"<br />
this<br />
is<br />
a<br />
block<br />
"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
or this<br />
<br />
<span class="default">&lt;?php<br />
$foo </span><span class="keyword">= </span><span class="string">"\nthis\nis\na\nblock\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Viewing it with the syntax highlighting switched on helps to make the difference clearer.</span>
</code></div>
  </div>
 </div>
 <a name="105369"></a>
 <div class="note">
  <strong class='user'>sgbeal at googlemail dot com</strong>
  <a href="#105369" class="date">12-Aug-2011 04:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The docs say: "Heredoc text behaves just like a double-quoted string, without the double quotes" but there is a notable hidden exception to that rule: the final newline in the string (the one before closing heredoc token) is elided. i.e. if you have:<br />
<br />
$foo = &lt;&lt;&lt;EOF<br />
a<br />
b<br />
c<br />
EOF;<br />
<br />
the result is equivalent to "a\nb\nc", NOT "a\nb\nc\n" like the docs imply.</span>
</code></div>
  </div>
 </div>
 <a name="105070"></a>
 <div class="note">
  <strong class='user'>KOmaSHOOTER at gmx dot de</strong>
  <a href="#105070" class="date">26-Jul-2011 01:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
curly brackets for strings<br />
<span class="default">&lt;?php<br />
$test_array </span><span class="keyword">= array(</span><span class="string">"hey"</span><span class="keyword">,</span><span class="string">"people"</span><span class="keyword">);<br />
</span><span class="default">$key </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
</span><span class="default">$key1 </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$letter </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
echo(</span><span class="default">$test_array</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">]{</span><span class="default">$letter</span><span class="keyword">});<br />
echo(</span><span class="default">$test_array</span><span class="keyword">[</span><span class="default">$key1</span><span class="keyword">]{</span><span class="default">$letter</span><span class="keyword">});<br />
</span><span class="default">?&gt;<br />
</span><br />
result: "hp"</span>
</code></div>
  </div>
 </div>
 <a name="103890"></a>
 <div class="note">
  <strong class='user'>Michael</strong>
  <a href="#103890" class="date">09-May-2011 05:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just want to mention that if you want a literal { around a variable within a string, for example if you want your output to be something like the following:<br />
<br />
{hello, world}<br />
<br />
and all that you put inside the {} is a variable, you can do a double {{}}, like this:<br />
<br />
$test = 'hello, world';<br />
echo "{{$test}}";</span>
</code></div>
  </div>
 </div>
 <a name="103680"></a>
 <div class="note">
  <strong class='user'>Ultimater at gmail dot com</strong>
  <a href="#103680" class="date">27-Apr-2011 03:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you require a NowDoc but don't have support for them on your server -- since your PHP version is less than PHP 5.3.0 -- and you are in need of a workaround, I'd suggest using PHP's __halt_compiler() which is basically a knock-off of Perl's __DATA__ token if you are familiar with it.<br />
<br />
Give this a run to see my suggestion in action:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//set $nowDoc to a string containing a code snippet for the user to read<br />
</span><span class="default">$nowDoc </span><span class="keyword">= </span><span class="default">file_get_contents</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">,</span><span class="default">null</span><span class="keyword">,</span><span class="default">null</span><span class="keyword">,</span><span class="default">__COMPILER_HALT_OFFSET__</span><span class="keyword">);<br />
</span><span class="default">$nowDoc</span><span class="keyword">=</span><span class="default">highlight_string</span><span class="keyword">(</span><span class="default">$nowDoc</span><span class="keyword">,</span><span class="default">true</span><span class="keyword">);<br />
<br />
echo &lt;&lt;&lt;EOF<br />
</span><span class="default">&lt;!doctype html&gt;<br />
&lt;html&gt;<br />
&lt;head&gt;<br />
&lt;meta http-equiv="content-type" content="text/html; charset=UTF-8" /&gt;<br />
&lt;title&gt;NowDoc support for PHP &amp;lt; 5.3.0&lt;/title&gt;<br />
&lt;meta name="author" content="Ultimater at gmail dot com" /&gt;<br />
&lt;meta name="about-this-page"<br />
content="Note that I built this code explicitly for the<br />
php.net documenation for demonstrative purposes." /&gt;<br />
&lt;style type="text/css"&gt;<br />
body{text-align:center;}<br />
table.border{background:#e0eaee;margin:1px auto;padding:1px;}<br />
table.border td{padding:5px;border:1px solid #8880ff;text-align:left;<br />
background-color:#eee;}<br />
code ::selection{background:#5f5color:white;}<br />
code ::-moz-selection{background:#5f5;color:white;}<br />
a{color:#33a;text-decoration:none;}<br />
a:hover{color:rgb(3,128,252);}<br />
&lt;/style&gt;<br />
&lt;/head&gt;<br />
&lt;body&gt;<br />
&lt;h1 style="margin:1px auto;"&gt;<br />
&lt;a<br />
href="<a href="http://php.net/manual/en/language.types.string.php#example-77" rel="nofollow" target="_blank">http://php.net/manual/en/language.types.string.php#example-77</a>"&gt;<br />
Example #8 Simple syntax example<br />
&lt;/a&gt;&lt;/h1&gt;<br />
&lt;table class="border"&gt;&lt;tr&gt;&lt;td&gt;<br />
$nowDoc<br />
&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;&lt;/body&gt;&lt;/html&gt;<br />
</span><span class="keyword">EOF;<br />
<br />
__halt_compiler()<br />
</span><span class="comment">//Example code snippet we want displayed on the webpage<br />
//note that the compiler isn't actually stopped until the semicolon<br />
</span><span class="keyword">;&lt;?</span><span class="default">php<br />
$juices </span><span class="keyword">= array(</span><span class="string">"apple"</span><span class="keyword">, </span><span class="string">"orange"</span><span class="keyword">, </span><span class="string">"koolaid1" </span><span class="keyword">=&gt; </span><span class="string">"purple"</span><span class="keyword">);<br />
<br />
echo </span><span class="string">"He drank some $juices[0] juice."</span><span class="keyword">.</span><span class="default">PHP_EOL</span><span class="keyword">;<br />
echo </span><span class="string">"He drank some $juices[1] juice."</span><span class="keyword">.</span><span class="default">PHP_EOL</span><span class="keyword">;<br />
echo </span><span class="string">"He drank some juice made of $juice[0]s."</span><span class="keyword">.</span><span class="default">PHP_EOL</span><span class="keyword">; </span><span class="comment">// Won't work<br />
</span><span class="keyword">echo </span><span class="string">"He drank some $juices[koolaid1] juice."</span><span class="keyword">.</span><span class="default">PHP_EOL</span><span class="keyword">;<br />
<br />
class </span><span class="default">people </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$john </span><span class="keyword">= </span><span class="string">"John Smith"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$jane </span><span class="keyword">= </span><span class="string">"Jane Smith"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$robert </span><span class="keyword">= </span><span class="string">"Robert Paulsen"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$smith </span><span class="keyword">= </span><span class="string">"Smith"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$people </span><span class="keyword">= new </span><span class="default">people</span><span class="keyword">();<br />
<br />
echo </span><span class="string">"$people-&gt;john drank some $juices[0] juice."</span><span class="keyword">.</span><span class="default">PHP_EOL</span><span class="keyword">;<br />
echo </span><span class="string">"$people-&gt;john then said hello to $people-&gt;jane."</span><span class="keyword">.</span><span class="default">PHP_EOL</span><span class="keyword">;<br />
echo </span><span class="string">"$people-&gt;john's wife greeted $people-&gt;robert."</span><span class="keyword">.</span><span class="default">PHP_EOL</span><span class="keyword">;<br />
echo </span><span class="string">"$people-&gt;robert greeted the two $people-&gt;smiths."</span><span class="keyword">; </span><span class="comment">// Won't work<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="102709"></a>
 <div class="note">
  <strong class='user'>dee jay simple 0 0 7 at  ge mahl  dot  com</strong>
  <a href="#102709" class="date">01-Mar-2011 12:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I recently discovered the joys of using heredoc with sprintf and positions. Useful if you want some code to iterate, you can repeat placeholders.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">getNumber</span><span class="keyword">(</span><span class="default">$num </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$foo </span><span class="keyword">= </span><span class="default">rand</span><span class="keyword">(</span><span class="default">1</span><span class="keyword">,</span><span class="default">20</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; return (</span><span class="default">$foo </span><span class="keyword">+ </span><span class="default">$num</span><span class="keyword">);<br />
}<br />
function </span><span class="default">getString</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$foo </span><span class="keyword">= array(</span><span class="string">"California"</span><span class="keyword">,</span><span class="string">"Oregon"</span><span class="keyword">,</span><span class="string">"Washington"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">shuffle</span><span class="keyword">(</span><span class="default">$foo</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$foo</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">];<br />
}<br />
function </span><span class="default">getDiv</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$num </span><span class="keyword">= </span><span class="default">getNumber</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$div </span><span class="keyword">= </span><span class="default">sprintf</span><span class="keyword">( </span><span class="string">"&lt;div&gt;%s&lt;/div&gt;"</span><span class="keyword">, </span><span class="default">getNumber</span><span class="keyword">(</span><span class="default">rand</span><span class="keyword">(-</span><span class="default">5</span><span class="keyword">,</span><span class="default">5</span><span class="keyword">)) );<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$div</span><span class="keyword">;<br />
}<br />
</span><span class="default">$string </span><span class="keyword">= &lt;&lt;&lt;THESTRING<br />
</span><span class="default">I like the state of %1\$s &lt;br /&gt;<br />
I picked: %2\$d as a number, &lt;br /&gt;<br />
I also picked %2\$d as a number again &lt;br /&gt;<br />
%3\$s&lt;br /&gt;<br />
%3\$s&lt;br /&gt;<br />
%3\$s&lt;br /&gt;<br />
%3\$s&lt;br /&gt;<br />
%3\$s&lt;br /&gt;<br />
</span><span class="keyword">THESTRING;<br />
<br />
</span><span class="default">$returnText </span><span class="keyword">= </span><span class="default">sprintf</span><span class="keyword">(&nbsp; </span><span class="default">$string</span><span class="keyword">, </span><span class="default">getString</span><span class="keyword">(),</span><span class="default">getNumber</span><span class="keyword">(),</span><span class="default">getDiv</span><span class="keyword">()&nbsp; );<br />
<br />
echo </span><span class="default">$returnText</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Expected output of the above code:<br />
<br />
I like the state of Oregon<br />
I picked: 15 as a number,<br />
I also picked 15 as a number again<br />
5<br />
<br />
5<br />
<br />
5<br />
<br />
5<br />
<br />
5</span>
</code></div>
  </div>
 </div>
 <a name="98140"></a>
 <div class="note">
  <strong class='user'>saamde at gmail dot com</strong>
  <a href="#98140" class="date">27-May-2010 03:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Watch out for the "unexpected T_SL" error.&nbsp; This appears to occur when there is white space just after "&lt;&lt;&lt;EOT" and since it's white space it's real hard to spot the error in your code.</span>
</code></div>
  </div>
 </div>
 <a name="95184"></a>
 <div class="note">
  <strong class='user'>&amp;#34;Sascha Ziemann&amp;#34;</strong>
  <a href="#95184" class="date">17-Dec-2009 01:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Empty strings seem to be no real strings, because they behave different to strings containing data. Here is an example.<br />
<br />
It is possible to change a character at a specific position using the square bracket notation:<br />
<span class="default">&lt;?php<br />
$str </span><span class="keyword">= </span><span class="string">'0'</span><span class="keyword">;<br />
</span><span class="default">$str</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">] = </span><span class="string">'a'</span><span class="keyword">;<br />
echo </span><span class="default">$str</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">; </span><span class="comment">// =&gt; 'a'<br />
</span><span class="default">?&gt;<br />
</span><br />
It is also possible to change a character with does not exist, if the index is "behind" the end of the string:<br />
<span class="default">&lt;?php<br />
$str </span><span class="keyword">= </span><span class="string">'0'</span><span class="keyword">;<br />
</span><span class="default">$str</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">] = </span><span class="string">'a'</span><span class="keyword">;<br />
echo </span><span class="default">$str</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">; </span><span class="comment">// =&gt; 0a<br />
</span><span class="default">?&gt;<br />
</span><br />
But if you do that on an empty string, the string gets silently converted into an array:<br />
<span class="default">&lt;?php<br />
$str </span><span class="keyword">= </span><span class="string">''</span><span class="keyword">;<br />
</span><span class="default">$str</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">] = </span><span class="string">'a'</span><span class="keyword">;<br />
echo </span><span class="default">$str</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">; </span><span class="comment">// =&gt; Array<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="94159"></a>
 <div class="note">
  <strong class='user'>shd at earthling dot net</strong>
  <a href="#94159" class="date">20-Oct-2009 02:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want a parsed variable surrounded by curly braces, just double the curly braces:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; $foo </span><span class="keyword">= </span><span class="string">"bar"</span><span class="keyword">;<br />
&nbsp; echo </span><span class="string">"{{$foo}}"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
will just show {bar}. The { is special only if followed by the $ sign and matches one }. In this case, that applies only to the inner braces. The outer ones are not escaped and pass through directly.</span>
</code></div>
  </div>
 </div>
 <a name="93576"></a>
 <div class="note">
  <strong class='user'>deminy at deminy dot net</strong>
  <a href="#93576" class="date">16-Sep-2009 09:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Although current documentation says 'A string literal can be specified in four different ways: ...', actually there is a fifth way to specify a (binary) string: <br />
<br />
<span class="default">&lt;?php $binary </span><span class="keyword">= </span><span class="string">b'This is a binary string'</span><span class="keyword">; </span><span class="default">?&gt;<br />
</span><br />
The above statement declares a binary string using the 'b' prefix, which is available since PHP 5.2.1. However, it will only have effect as of PHP 6.0.0, as noted on <a href="http://www.php.net/manual/en/function.is-binary.php" rel="nofollow" target="_blank">http://www.php.net/manual/en/function.is-binary.php</a> .</span>
</code></div>
  </div>
 </div>
 <a name="93325"></a>
 <div class="note">
  <strong class='user'>Liesbeth</strong>
  <a href="#93325" class="date">03-Sep-2009 01:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you need to emulate a nowdoc in PHP &lt; 5.3, try using HTML mode and output capturing. This way '$' or '\n' in your string won't be a problem anymore (but unfortunately, '&lt;?' will be).<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// Start of script<br />
<br />
</span><span class="default">ob_start</span><span class="keyword">(); </span><span class="default">?&gt;<br />
</span>&nbsp; A text with 'quotes' <br />
&nbsp;&nbsp;&nbsp; and $$$dollars$$$.<br />
<span class="default">&lt;?php $input </span><span class="keyword">= </span><span class="default">ob_get_contents</span><span class="keyword">(); </span><span class="default">ob_end_clean</span><span class="keyword">();<br />
<br />
</span><span class="comment">// Do what you want with $input<br />
</span><span class="keyword">echo </span><span class="string">"&lt;pre&gt;" </span><span class="keyword">. </span><span class="default">$input </span><span class="keyword">. </span><span class="string">"&lt;/pre&gt;"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="91628"></a>
 <div class="note">
  <strong class='user'>headden at karelia dot ru</strong>
  <a href="#91628" class="date">20-Jun-2009 12:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here is an easy hack to allow double-quoted strings and heredocs to contain arbitrary expressions in curly braces syntax, including constants and other function calls:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// Hack declaration<br />
</span><span class="keyword">function </span><span class="default">_expr</span><span class="keyword">(</span><span class="default">$v</span><span class="keyword">) { return </span><span class="default">$v</span><span class="keyword">; }<br />
</span><span class="default">$_expr </span><span class="keyword">= </span><span class="string">'_expr'</span><span class="keyword">;<br />
<br />
</span><span class="comment">// Our playground<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'qwe'</span><span class="keyword">, </span><span class="string">'asd'</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'zxc'</span><span class="keyword">, </span><span class="default">5</span><span class="keyword">);<br />
<br />
</span><span class="default">$a</span><span class="keyword">=</span><span class="default">3</span><span class="keyword">;<br />
</span><span class="default">$b</span><span class="keyword">=</span><span class="default">4</span><span class="keyword">;<br />
<br />
function </span><span class="default">c</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">) { return </span><span class="default">$a</span><span class="keyword">+</span><span class="default">$b</span><span class="keyword">; }<br />
<br />
</span><span class="comment">// Usage<br />
</span><span class="keyword">echo </span><span class="string">"pre {$_expr(1+2)} post\n"</span><span class="keyword">; </span><span class="comment">// outputs 'pre 3 post'<br />
</span><span class="keyword">echo </span><span class="string">"pre {$_expr(qwe)} post\n"</span><span class="keyword">; </span><span class="comment">// outputs 'pre asd post'<br />
</span><span class="keyword">echo </span><span class="string">"pre {$_expr(c($a, $b)+zxc*2)} post\n"</span><span class="keyword">; </span><span class="comment">// outputs 'pre 17 post'<br />
<br />
// General syntax is {$_expr(...)}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="87390"></a>
 <div class="note">
  <strong class='user'>cvolny at gmail dot com</strong>
  <a href="#87390" class="date">02-Dec-2008 11:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I commented on a php bug feature request for a string expansion function and figured I should post somewhere it might be useful:<br />
<br />
using regex, pretty straightforward:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">stringExpand</span><span class="keyword">(</span><span class="default">$subject</span><span class="keyword">, array </span><span class="default">$vars</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// loop over $vars map<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">foreach (</span><span class="default">$vars </span><span class="keyword">as </span><span class="default">$name </span><span class="keyword">=&gt; </span><span class="default">$value</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// use preg_replace to match ${`$name`} or $`$name`<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$subject </span><span class="keyword">= </span><span class="default">preg_replace</span><span class="keyword">(</span><span class="default">sprintf</span><span class="keyword">(</span><span class="string">'/\$\{?%s\}?/'</span><span class="keyword">, </span><span class="default">$name</span><span class="keyword">), </span><span class="default">$value</span><span class="keyword">,<br />
</span><span class="default">$subject</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// return variable expanded string<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">$subject</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
using eval() and not limiting access to only certain variables (entire current symbol table including [super]globals):<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">stringExpandDangerous</span><span class="keyword">(</span><span class="default">$subject</span><span class="keyword">, array </span><span class="default">$vars </span><span class="keyword">= array(), </span><span class="default">$random </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// extract $vars into current symbol table<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">extract</span><span class="keyword">(</span><span class="default">$vars</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$delim</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// if requested to be random (default), generate delim, otherwise use predefined (trivially faster)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if (</span><span class="default">$random</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$delim </span><span class="keyword">= </span><span class="string">'___' </span><span class="keyword">. </span><span class="default">chr</span><span class="keyword">(</span><span class="default">mt_rand</span><span class="keyword">(</span><span class="default">65</span><span class="keyword">,</span><span class="default">90</span><span class="keyword">)) . </span><span class="default">chr</span><span class="keyword">(</span><span class="default">mt_rand</span><span class="keyword">(</span><span class="default">65</span><span class="keyword">,</span><span class="default">90</span><span class="keyword">)) . </span><span class="default">chr</span><span class="keyword">(</span><span class="default">mt_rand</span><span class="keyword">(</span><span class="default">65</span><span class="keyword">,</span><span class="default">90</span><span class="keyword">)) . </span><span class="default">chr</span><span class="keyword">(</span><span class="default">mt_rand</span><span class="keyword">(</span><span class="default">65</span><span class="keyword">,</span><span class="default">90</span><span class="keyword">)) . </span><span class="default">chr</span><span class="keyword">(</span><span class="default">mt_rand</span><span class="keyword">(</span><span class="default">65</span><span class="keyword">,</span><span class="default">90</span><span class="keyword">)) . </span><span class="string">'___'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$delim </span><span class="keyword">= </span><span class="string">'__ASDFZXCV1324ZXCV__'</span><span class="keyword">;&nbsp; </span><span class="comment">// button mashing...<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; // built the eval code<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$statement </span><span class="keyword">= </span><span class="string">"return &lt;&lt;&lt;$delim\n\n" </span><span class="keyword">. </span><span class="default">$subject </span><span class="keyword">. </span><span class="string">"\n$delim;\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// execute statement, saving output to $result variable<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$result </span><span class="keyword">= eval(</span><span class="default">$statement</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// if eval() returned FALSE, throw a custom exception<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if (</span><span class="default">$result </span><span class="keyword">=== </span><span class="default">false</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; throw new </span><span class="default">EvalException</span><span class="keyword">(</span><span class="default">$statement</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// return variable expanded string<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">$result</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;<br />
</span><br />
I hope that helps someone, but I do caution against using the eval() route even if it is tempting.&nbsp; I don't know if there's ever a truely safe way to use eval() on the web, I'd rather not use it.</span>
</code></div>
  </div>
 </div>
 <a name="87035"></a>
 <div class="note">
  <strong class='user'>Obeliks</strong>
  <a href="#87035" class="date">15-Nov-2008 08:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Expectedly <span class="default">&lt;?php $string</span><span class="keyword">[</span><span class="default">$x</span><span class="keyword">] </span><span class="default">?&gt;</span> and <span class="default">&lt;?php substr</span><span class="keyword">(</span><span class="default">$string</span><span class="keyword">, </span><span class="default">$x</span><span class="keyword">, </span><span class="default">1</span><span class="keyword">) </span><span class="default">?&gt;</span> will yield the same result... normally!<br />
<br />
However, when you turn on the&nbsp; Function Overloading Feature (<a href="http://php.net/manual/en/mbstring.overload.php" rel="nofollow" target="_blank">http://php.net/manual/en/mbstring.overload.php</a>), this might not be true!<br />
<br />
If you use this Overloading Feature with 3rd party software, you should check for usage of the String access operator, otherwise you might be in for some nasty surprises.</span>
</code></div>
  </div>
 </div>
 <a name="86365"></a>
 <div class="note">
  <strong class='user'>Salil Kothadia</strong>
  <a href="#86365" class="date">15-Oct-2008 01:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
An interesting finding about Heredoc "syntax error, unexpected $end".<br />
I got this error because I did not use the php close tag "?&gt;" and I had no code after the heredoc code.<br />
<br />
foo1.php code gives "syntax error, unexpected $end".<br />
But in foo2.php and foo3.php, when you add a php close tag or when you have some more code after heredoc it works fine.<br />
<br />
Example Code:<br />
foo1.php<br />
1. <span class="default">&lt;?php<br />
2. $str </span><span class="keyword">= &lt;&lt;&lt;EOD<br />
</span><span class="default">3. Example of string<br />
4. spanning multiple lines<br />
5. using heredoc syntax.<br />
6. EOD;<br />
7. <br />
<br />
foo2.php<br />
1. &lt;?php<br />
2. $str = &lt;&lt;&lt;EOD<br />
3. Example of string<br />
4. spanning multiple lines<br />
5. using heredoc syntax.<br />
6. EOD;<br />
7. <br />
8. echo $str;<br />
9.<br />
<br />
foo3.php<br />
1. &lt;?php<br />
2. $str = &lt;&lt;&lt;EOD<br />
3. Example of string<br />
4. spanning multiple lines<br />
5. using heredoc syntax.<br />
6. EOD;<br />
7. ?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="86044"></a>
 <div class="note">
  <strong class='user'>steve at mrclay dot org</strong>
  <a href="#86044" class="date">30-Sep-2008 01:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Simple function to create human-readably escaped double-quoted strings for use in source code or when debugging strings with newlines/tabs/etc.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">doubleQuote</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$ret </span><span class="keyword">= </span><span class="string">'"'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">, </span><span class="default">$l </span><span class="keyword">= </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">); </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">$l</span><span class="keyword">; ++</span><span class="default">$i</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$o </span><span class="keyword">= </span><span class="default">ord</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">]);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$o </span><span class="keyword">&lt; </span><span class="default">31 </span><span class="keyword">|| </span><span class="default">$o </span><span class="keyword">&gt; </span><span class="default">126</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; switch (</span><span class="default">$o</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">9</span><span class="keyword">: </span><span class="default">$ret </span><span class="keyword">.= </span><span class="string">'\t'</span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">10</span><span class="keyword">: </span><span class="default">$ret </span><span class="keyword">.= </span><span class="string">'\n'</span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">11</span><span class="keyword">: </span><span class="default">$ret </span><span class="keyword">.= </span><span class="string">'\v'</span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">12</span><span class="keyword">: </span><span class="default">$ret </span><span class="keyword">.= </span><span class="string">'\f'</span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">13</span><span class="keyword">: </span><span class="default">$ret </span><span class="keyword">.= </span><span class="string">'\r'</span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; default: </span><span class="default">$ret </span><span class="keyword">.= </span><span class="string">'\x' </span><span class="keyword">. </span><span class="default">str_pad</span><span class="keyword">(</span><span class="default">dechex</span><span class="keyword">(</span><span class="default">$o</span><span class="keyword">), </span><span class="default">2</span><span class="keyword">, </span><span class="string">'0'</span><span class="keyword">, </span><span class="default">STR_PAD_LEFT</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; switch (</span><span class="default">$o</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">36</span><span class="keyword">: </span><span class="default">$ret </span><span class="keyword">.= </span><span class="string">'\$'</span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">34</span><span class="keyword">: </span><span class="default">$ret </span><span class="keyword">.= </span><span class="string">'\"'</span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">92</span><span class="keyword">: </span><span class="default">$ret </span><span class="keyword">.= </span><span class="string">'\\\\'</span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; default: </span><span class="default">$ret </span><span class="keyword">.= </span><span class="default">$str</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$ret </span><span class="keyword">. </span><span class="string">'"'</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85668"></a>
 <div class="note">
  <strong class='user'>chAlx at findme dot if dot u dot need</strong>
  <a href="#85668" class="date">11-Sep-2008 08:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To save Your mind don't read previous comments about dates&nbsp; ;)<br />
<br />
When both strings can be converted to the numerics (in ("$a" &gt; "$b") test) then resulted numerics are used, else FULL strings are compared char-by-char:<br />
<br />
<span class="default">&lt;?php<br />
var_dump</span><span class="keyword">(</span><span class="string">'1.22' </span><span class="keyword">&gt; </span><span class="string">'01.23'</span><span class="keyword">); </span><span class="comment">// bool(false)<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="string">'1.22.00' </span><span class="keyword">&gt; </span><span class="string">'01.23.00'</span><span class="keyword">); </span><span class="comment">// bool(true)<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="string">'1-22-00' </span><span class="keyword">&gt; </span><span class="string">'01-23-00'</span><span class="keyword">); </span><span class="comment">// bool(true)<br />
</span><span class="default">var_dump</span><span class="keyword">((float)</span><span class="string">'1.22.00' </span><span class="keyword">&gt; (float)</span><span class="string">'01.23.00'</span><span class="keyword">); </span><span class="comment">// bool(false)<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85477"></a>
 <div class="note">
  <strong class='user'>harmor</strong>
  <a href="#85477" class="date">01-Sep-2008 03:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
So you want to get the last character of a string using "String access and modification by character"?&nbsp; Well negative indexes are not allowed so $str[-1] will return an empty string.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//Tested using: PHP 5.2.5<br />
<br />
</span><span class="default">$str </span><span class="keyword">= </span><span class="string">'This is a test.'</span><span class="keyword">;<br />
<br />
</span><span class="default">$last </span><span class="keyword">= </span><span class="default">$str</span><span class="keyword">[-</span><span class="default">1</span><span class="keyword">];&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">//string(0) ""<br />
</span><span class="default">$realLast </span><span class="keyword">= </span><span class="default">$str</span><span class="keyword">[</span><span class="default">strlen</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">)-</span><span class="default">1</span><span class="keyword">];&nbsp; </span><span class="comment">//string(1) "."<br />
</span><span class="default">$substr </span><span class="keyword">= </span><span class="default">substr</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">,-</span><span class="default">1</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//string(1) "."<br />
<br />
</span><span class="keyword">echo </span><span class="string">'&lt;pre&gt;'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$last</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$realLast</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$substr</span><span class="keyword">);</span>
</span>
</code></div>
  </div>
 </div>
 <a name="83657"></a>
 <div class="note">
  <strong class='user'>nullhility at gmail dot com</strong>
  <a href="#83657" class="date">06-Jun-2008 12:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It's also valuable to note the following:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">${</span><span class="default">date</span><span class="keyword">(</span><span class="string">"M"</span><span class="keyword">)} = </span><span class="string">"Worked"</span><span class="keyword">;<br />
echo ${</span><span class="default">date</span><span class="keyword">(</span><span class="string">"M"</span><span class="keyword">)};<br />
</span><span class="default">?&gt;<br />
</span><br />
This is perfectly legal, anything inside the braces is executed first, the return value then becomes the variable name. Echoing the same variable variable using the function that created it results in the same return and therefore the same variable name is used in the echo statement. Have fun ;).</span>
</code></div>
  </div>
 </div>
 <a name="81457"></a>
 <div class="note">
  <strong class='user'>Evan K</strong>
  <a href="#81457" class="date">28-Feb-2008 01:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I encountered the odd situation of having a string containing unexpanded escape sequences that I wanted to expand, but also contained dollar signs that would be interpolated as variables.&nbsp; "$5.25\n", for example, where I want to convert \n to a newline, but don't want attempted interpolation of $5.<br />
<br />
Some muddling through docs and many obscenties later, I produced the following, which expands escape sequences in an existing string with NO interpolation.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// where we do all our magic<br />
</span><span class="keyword">function </span><span class="default">expand_escape</span><span class="keyword">(</span><span class="default">$string</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">preg_replace_callback</span><span class="keyword">(<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'/\\\([nrtvf]|[0-7]{1,3}|[0-9A-Fa-f]{1,2})?/'</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">create_function</span><span class="keyword">(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'$matches'</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'return ($matches[0] == "\\\\") ? "" : eval( sprintf(\'return "%s";\', $matches[0]) );'<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">),<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$string<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">);<br />
}<br />
<br />
</span><span class="comment">// a string to test, and show the before and after<br />
</span><span class="default">$before </span><span class="keyword">= </span><span class="string">'Quantity:\t500\nPrice:\t$5.25 each'</span><span class="keyword">;<br />
</span><span class="default">$after </span><span class="keyword">= </span><span class="default">expand_escape</span><span class="keyword">(</span><span class="default">$before</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$before</span><span class="keyword">, </span><span class="default">$after</span><span class="keyword">);<br />
<br />
</span><span class="comment">/* Outputs:<br />
string(34) "Quantity:\t500\nPrice:\t$5.25 each"<br />
string(31) "Quantity:&nbsp; &nbsp; 500<br />
Price:&nbsp; &nbsp; $5.25 each"<br />
*/<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="80925"></a>
 <div class="note">
  <strong class='user'> dot  dot  dot  dot  dot alexander at gmail dot com</strong>
  <a href="#80925" class="date">06-Feb-2008 10:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I think there's not that much to string comparison as claiming date recognition:<br />
<br />
It's simply comparing ordinal values of the characters from the {0} to the {strlen-1} one.<br />
In this case <br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="string">'2007-11-06 15:17:48'</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="string">'2007-11-05 15:17:48'</span><span class="keyword">;<br />
<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a </span><span class="keyword">&gt; </span><span class="default">$b</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span>mArIo@luigi ~ $: php test.php<br />
bool(true)<br />
here all characters match till it reaches position 9 (the "day")<br />
there, 6 has a bigger ord()inal value than 5 <br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="string">'January 25th, 2008 00:23:38'</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="string">'Janury 24th, 2008 00:23:37'</span><span class="keyword">; </span><span class="comment">// ($a &gt; $b) === false<br />
</span><span class="default">?&gt;<br />
</span>Here when we reach 'r' in "Janury" we see that "a" is "less" than "r" so the example would evaluate as ($a &lt; $b) === true<br />
<br />
Here:<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="string">'February 1st, 2008 00:23:38'</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="string">'January 25th, 2008 00:23:38'</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span>as expected the letter "F" comes before "J" as an ordinal character, so $a is less than $b<br />
&nbsp;Even here:<br />
<span class="default">&lt;?php<br />
var_dump</span><span class="keyword">(</span><span class="string">'Z' </span><span class="keyword">&gt; </span><span class="string">'M'</span><span class="keyword">); </span><span class="comment">//bool(true)<br />
</span><span class="default">?&gt;<br />
</span>it gets confirmed that the string comparison operators &gt;, &lt;, =&gt;, =&lt;, == just do a ordinal character comparison starting from position {0} to the first difference or the end of the string.</span>
</code></div>
  </div>
 </div>
 <a name="78067"></a>
 <div class="note">
  <strong class='user'>rkfranklin+php at gmail dot com</strong>
  <a href="#78067" class="date">26-Sep-2007 12:35</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to use a variable in an array index within a double quoted string you have to realize that when you put the curly braces around the array, everything inside the curly braces gets evaluated as if it were outside a string.&nbsp; Here are some examples:<br />
<br />
<span class="default">&lt;?php<br />
$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
</span><span class="default">$myArray</span><span class="keyword">[</span><span class="default">Person0</span><span class="keyword">] = </span><span class="default">Bob</span><span class="keyword">;<br />
</span><span class="default">$myArray</span><span class="keyword">[</span><span class="default">Person1</span><span class="keyword">] = </span><span class="default">George</span><span class="keyword">;<br />
<br />
</span><span class="comment">// prints Bob (the ++ is used to emphasize that the expression inside the {} is really being evaluated.)<br />
</span><span class="keyword">echo </span><span class="string">"{$myArray['Person'.$i++]}&lt;br&gt;"</span><span class="keyword">;<br />
<br />
</span><span class="comment">// these print George<br />
</span><span class="keyword">echo </span><span class="string">"{$myArray['Person'.$i]}&lt;br&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"{$myArray["</span><span class="default">Person</span><span class="keyword">{</span><span class="default">$i</span><span class="keyword">}</span><span class="string">"]}&lt;br&gt;"</span><span class="keyword">;<br />
<br />
</span><span class="comment">// These don't work<br />
</span><span class="keyword">echo </span><span class="string">"{$myArray['Person$i']}&lt;br&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"{$myArray['Person'$i]}&lt;br&gt;"</span><span class="keyword">;<br />
<br />
</span><span class="comment">// These both throw fatal errors<br />
// echo "$myArray[Person$i]&lt;br&gt;";<br />
//echo "$myArray[Person{$i}]&lt;br&gt;";<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="75478"></a>
 <div class="note">
  <strong class='user'>Richard Neill</strong>
  <a href="#75478" class="date">31-May-2007 08:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Unlike bash, we can't do <br />
&nbsp; echo "\a"&nbsp; &nbsp; &nbsp;&nbsp; #beep!<br />
<br />
Of course, that would be rather meaningless for PHP/web, but it's useful for PHP-CLI. The solution is simple:&nbsp; echo "\x07"</span>
</code></div>
  </div>
 </div>
 <a name="74744"></a>
 <div class="note">
  <strong class='user'>og at gams dot at</strong>
  <a href="#74744" class="date">25-Apr-2007 05:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
easy transparent solution for using constants in the heredoc format:<br />
DEFINE('TEST','TEST STRING');<br />
<br />
$const = get_defined_constants();<br />
<br />
echo &lt;&lt;&lt;END<br />
{$const['TEST']}<br />
END;<br />
<br />
Result:<br />
TEST STRING</span>
</code></div>
  </div>
 </div>
 <a name="74710"></a>
 <div class="note">
  <strong class='user'>penda ekoka</strong>
  <a href="#74710" class="date">24-Apr-2007 10:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
error control operator (@) with heredoc syntax:<br />
<br />
the error control operator is pretty handy for supressing minimal errors or omissions. For example an email form that request some basic non mandatory information to your users. Some may complete the form, other may not. Lets say you don't want to tweak PHP for error levels and you just wish to create some basic template that will be emailed to the admin with the user information submitted. You manage to collect the user input in an array called $form:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// creating your mailer<br />
</span><span class="default">$mailer </span><span class="keyword">= new </span><span class="default">SomeMailerLib</span><span class="keyword">();<br />
</span><span class="default">$mailer</span><span class="keyword">-&gt;</span><span class="default">from </span><span class="keyword">= </span><span class="string">' System &lt;mail@yourwebsite.com&gt;'</span><span class="keyword">;<br />
</span><span class="default">$mailer</span><span class="keyword">-&gt;</span><span class="default">to </span><span class="keyword">= </span><span class="string">'admin@yourwebsite.com'</span><span class="keyword">;<br />
</span><span class="default">$mailer</span><span class="keyword">-&gt;</span><span class="default">subject </span><span class="keyword">= </span><span class="string">'New user request'</span><span class="keyword">;<br />
</span><span class="comment">// you put the error control operator before the heredoc operator to suppress notices and warnings about unset indices like this<br />
</span><span class="default">$mailer</span><span class="keyword">-&gt;</span><span class="default">body </span><span class="keyword">= @&lt;&lt;&lt;FORM<br />
</span><span class="default">Firstname = </span><span class="keyword">{</span><span class="default">$form</span><span class="keyword">[</span><span class="string">'firstname'</span><span class="keyword">]}</span><span class="default"><br />
Lastname = </span><span class="keyword">{</span><span class="default">$form</span><span class="keyword">[</span><span class="string">'lastname'</span><span class="keyword">]}</span><span class="default"><br />
Email = </span><span class="keyword">{</span><span class="default">$form</span><span class="keyword">[</span><span class="string">'email'</span><span class="keyword">]}</span><span class="default"><br />
Telephone = </span><span class="keyword">{</span><span class="default">$form</span><span class="keyword">[</span><span class="string">'telephone'</span><span class="keyword">]}</span><span class="default"><br />
Address = </span><span class="keyword">{</span><span class="default">$form</span><span class="keyword">[</span><span class="string">'address'</span><span class="keyword">]}</span><span class="default"><br />
</span><span class="keyword">FORM;<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="74257"></a>
 <div class="note">
  <strong class='user'>php at moechofe dot com</strong>
  <a href="#74257" class="date">01-Apr-2007 08:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A simple benchmark to check differents about :<br />
- simple and double quote concatenation and<br />
- double quote and heredoc replacement<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">test_simple_quote_concat</span><span class="keyword">()<br />
{<br />
&nbsp; </span><span class="default">$b </span><span class="keyword">= </span><span class="string">'string'</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a&nbsp; </span><span class="keyword">= </span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' srting'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">' string'</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
}<br />
<br />
function </span><span class="default">test_double_quote_concat</span><span class="keyword">()<br />
{<br />
&nbsp; </span><span class="default">$b </span><span class="keyword">= </span><span class="string">"string"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a&nbsp; </span><span class="keyword">= </span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">.= </span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="string">" string"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
}<br />
<br />
function </span><span class="default">test_double_quote_replace</span><span class="keyword">()<br />
{<br />
&nbsp; </span><span class="default">$b </span><span class="keyword">= </span><span class="string">"string"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">= </span><span class="string">" string$b string$b string$b <br />
string$b string$b string$b <br />
string$b string$b string$b <br />
string$b string$b string$b <br />
string$b string$b string$b <br />
string$b string$b string$b <br />
string$b string$b string$b <br />
string$b string$b string$b"</span><span class="keyword">;<br />
}<br />
<br />
function </span><span class="default">test_eot_replace</span><span class="keyword">()<br />
{<br />
&nbsp; </span><span class="default">$b </span><span class="keyword">= &lt;&lt;&lt;EOT<br />
</span><span class="default">string<br />
</span><span class="keyword">EOT;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">= &lt;&lt;&lt;EOT<br />
</span><span class="default">string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> <br />
string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> <br />
string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> <br />
string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"><br />
string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> <br />
string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"><br />
string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> <br />
string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"> string</span><span class="keyword">{</span><span class="default">$b</span><span class="keyword">}</span><span class="default"><br />
</span><span class="keyword">EOT;<br />
}<br />
<br />
</span><span class="default">$iter </span><span class="keyword">= </span><span class="default">2000</span><span class="keyword">;<br />
<br />
for( </span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">$iter</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++ )<br />
&nbsp; </span><span class="default">test_simple_quote_concat</span><span class="keyword">();<br />
<br />
for( </span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">$iter</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++ )<br />
&nbsp; </span><span class="default">test_double_quote_concat</span><span class="keyword">();<br />
<br />
for( </span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">$iter</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++ )<br />
&nbsp; </span><span class="default">test_double_quote_replace</span><span class="keyword">();<br />
<br />
for( </span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">$iter</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++ )<br />
&nbsp; </span><span class="default">test_eot_replace</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
I've use xdebug profiler to obtain the followed results:<br />
<br />
test_simple_quote_concat : 173ms<br />
test_double_quote_concat : 161ms<br />
test_double_quote_replace : 147ms<br />
test_eot_replace : 130ms</span>
</code></div>
  </div>
 </div>
 <a name="73524"></a>
 <div class="note">
  <strong class='user'>bryant at zionprogramming dot com</strong>
  <a href="#73524" class="date">27-Feb-2007 12:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As of (at least) PHP 5.2, you can no longer convert an object to a string unless it has a __toString method. Converting an object without this method now gives the error:<br />
<br />
PHP Catchable fatal error:&nbsp; Object of class &lt;classname&gt; could not be converted to string in &lt;file&gt; on line &lt;line&gt;<br />
<br />
Try this code to get the same results as before:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">if (!</span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">) || </span><span class="default">method_exists</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">, </span><span class="string">'__toString'</span><span class="keyword">)) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$string </span><span class="keyword">= (string)</span><span class="default">$value</span><span class="keyword">;<br />
} else {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$string </span><span class="keyword">= </span><span class="string">'Object'</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="73400"></a>
 <div class="note">
  <strong class='user'>fmouse at fmp dot com</strong>
  <a href="#73400" class="date">21-Feb-2007 10:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It may be obvious to some, but it's convenient to note that variables _will_ be expanded inside of single quotes if these occur inside of a double-quoted string.&nbsp; This can be handy in constructing exec calls with complex data to be passed to other programs.&nbsp; e.g.:<br />
<br />
$foo = "green";<br />
echo "the grass is $foo";<br />
the grass is green<br />
<br />
echo 'the grass is $foo';<br />
the grass is $foo<br />
<br />
echo "the grass is '$foo'";<br />
the grass is 'green'</span>
</code></div>
  </div>
 </div>
 <a name="63707"></a>
 <div class="note">
  <strong class='user'>bishop</strong>
  <a href="#63707" class="date">28-Mar-2006 12:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You may use heredoc syntax to comment out large blocks of code, as follows:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">&lt;&lt;&lt;_EOC<br />
</span><span class="default">&nbsp; &nbsp; // end-of-line comment will be masked... so will regular PHP:<br />
&nbsp;&nbsp;&nbsp; echo ($test == 'foo' ? 'bar' : 'baz'); <br />
&nbsp;&nbsp;&nbsp; /* c-style comment will be masked, as will other heredocs (not using the same marker) */<br />
&nbsp;&nbsp;&nbsp; echo &lt;&lt;&lt;EOHTML<br />
This is text you'll never see!&nbsp; &nbsp; &nbsp; &nbsp; <br />
EOHTML;<br />
&nbsp;&nbsp;&nbsp; function defintion($params) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo 'foo';<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; class definition extends nothing&nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; function definition($param) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo 'do nothing';<br />
&nbsp;&nbsp; &nbsp; &nbsp; }&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; how about syntax errors?; = gone, I bet.<br />
</span><span class="keyword">_EOC;<br />
</span><span class="default">?&gt;<br />
</span><br />
Useful for debugging when C-style just won't do.&nbsp; Also useful if you wish to embed Perl-like Plain Old Documentation; extraction between POD markers is left as an exercise for the reader.<br />
<br />
Note there is a performance penalty for this method, as PHP must still parse and variable substitute the string.</span>
</code></div>
  </div>
 </div>
 <a name="59248"></a>
 <div class="note">
  <strong class='user'>webmaster at rephunter dot net</strong>
  <a href="#59248" class="date">30-Nov-2005 08:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Use caution when you need white space at the end of a heredoc. Not only is the mandatory final newline before the terminating symbol stripped, but an immediately preceding newline or space character is also stripped.<br />
<br />
For example, in the following, the final space character (indicated by \s -- that is, the "\s" is not literally in the text, but is only used to indicate the space character) is stripped:<br />
<br />
$string = &lt;&lt;&lt;EOT<br />
this is a string with a terminating space\s<br />
EOT;<br />
<br />
In the following, there will only be a single newline at the end of the string, even though two are shown in the text:<br />
<br />
$string = &lt;&lt;&lt;EOT<br />
this is a string that must be<br />
followed by a single newline<br />
<br />
EOT;</span>
</code></div>
  </div>
 </div>
 <a name="58353"></a>
 <div class="note">
  <strong class='user'>DELETETHIS dot php at dfackrell dot mailshell dot com</strong>
  <a href="#58353" class="date">01-Nov-2005 08:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just some quick observations on variable interpolation:<br />
<br />
Because PHP looks for {? to start a complex variable expression in a double-quoted string, you can call object methods, but not class methods or unbound functions.<br />
<br />
This works:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">a </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">b</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"World"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">$c </span><span class="keyword">= new </span><span class="default">a</span><span class="keyword">;<br />
echo </span><span class="string">"Hello {$c-&gt;b()}.\n"<br />
</span><span class="default">?&gt;<br />
</span><br />
While this does not:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">b</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">"World"</span><span class="keyword">;<br />
}<br />
echo </span><span class="string">"Hello {b()}\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Also, it appears that you can almost without limitation perform other processing within the argument list, but not outside it.&nbsp; For example:<br />
<br />
&lt;?<br />
$true = true;<br />
define("HW", "Hello World");<br />
echo "{$true &amp;&amp; HW}";<br />
?&gt;<br />
<br />
gives: Parse error: parse error, unexpected T_BOOLEAN_AND, expecting '}' in - on line 3<br />
<br />
There may still be some way to kludge the syntax to allow constants and unbound function calls inside a double-quoted string, but it isn't readily apparent to me at the moment, and I'm not sure I'd prefer the workaround over breaking out of the string at this point.</span>
</code></div>
  </div>
 </div>
 <a name="46914"></a>
 <div class="note">
  <strong class='user'>lelon at lelon dot net</strong>
  <a href="#46914" class="date">27-Oct-2004 12:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can use the complex syntax to put the value of both object properties AND object methods inside a string.&nbsp; For example...<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Test </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$one </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">two</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">2</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">$test </span><span class="keyword">= new </span><span class="default">Test</span><span class="keyword">();<br />
echo </span><span class="string">"foo {$test-&gt;one} bar {$test-&gt;two()}"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span>Will output "foo 1 bar 2".<br />
<br />
However, you cannot do this for all values in your namespace.&nbsp; Class constants and static properties/methods will not work because the complex syntax looks for the '$'.<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Test </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">ONE </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
}<br />
echo </span><span class="string">"foo {Test::ONE} bar"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span>This will output "foo {Test::one} bar".&nbsp; Constants and static properties require you to break up the string.</span>
</code></div>
  </div>
 </div>
 <a name="44458"></a>
 <div class="note">
  <strong class='user'>Jonathan Lozinski</strong>
  <a href="#44458" class="date">06-Aug-2004 12:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A note on the heredoc stuff.<br />
<br />
If you're editing with VI/VIM and possible other syntax highlighting editors, then using certain words is the way forward.&nbsp; if you use &lt;&lt;&lt;HTML for example, then the text will be hightlighted for HTML!!<br />
<br />
I just found this out and used sed to alter all EOF to HTML.<br />
<br />
JAVASCRIPT also works, and possibly others.&nbsp; The only thing about &lt;&lt;&lt;JAVASCRIPT is that you can't add the &lt;script&gt; tags..,&nbsp; so use HTML instead, which will correctly highlight all JavaScript too..<br />
<br />
You can also use EOHTML, EOSQL, and EOJAVASCRIPT.</span>
</code></div>
  </div>
 </div>
 <a name="41986"></a>
 <div class="note">
  <strong class='user'>www.feisar.de</strong>
  <a href="#41986" class="date">28-Apr-2004 07:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
watch out when comparing strings that are numbers. this example:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$x1 </span><span class="keyword">= </span><span class="string">'111111111111111111'</span><span class="keyword">;<br />
</span><span class="default">$x2 </span><span class="keyword">= </span><span class="string">'111111111111111112'</span><span class="keyword">;<br />
<br />
echo (</span><span class="default">$x1 </span><span class="keyword">== </span><span class="default">$x2</span><span class="keyword">) ? </span><span class="string">"true\n" </span><span class="keyword">: </span><span class="string">"false\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
will output "true", although the strings are different. With large integer-strings, it seems that PHP compares only the integer values, not the strings. Even strval() will not work here.<br />
<br />
To be on the safe side, use:<br />
<br />
$x1 === $x2</span>
</code></div>
  </div>
 </div>
 <a name="41470"></a>
 <div class="note">
  <strong class='user'>atnak at chejz dot com</strong>
  <a href="#41470" class="date">11-Apr-2004 03:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here is a possible gotcha related to oddness involved with accessing strings by character past the end of the string:<br />
<br />
$string = 'a';<br />
<br />
var_dump($string[2]);&nbsp; // string(0) ""<br />
var_dump($string[7]);&nbsp; // string(0) ""<br />
$string[7] === '';&nbsp; // TRUE<br />
<br />
It appears that anything past the end of the string gives an empty string..&nbsp; However, when E_NOTICE is on, the above examples will throw the message:<br />
<br />
Notice:&nbsp; Uninitialized string offset:&nbsp; N in FILE on line LINE<br />
<br />
This message cannot be specifically masked with @$string[7], as is possible when $string itself is unset.<br />
<br />
isset($string[7]);&nbsp; // FALSE<br />
$string[7] === NULL;&nbsp; // FALSE<br />
<br />
Even though it seems like a not-NULL value of type string, it is still considered unset.</span>
</code></div>
  </div>
 </div>
 <a name="26537"></a>
 <div class="note">
  <strong class='user'>vallo at cs dot helsinki dot fi</strong>
  <a href="#26537" class="date">03-Nov-2002 05:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Even if the correct way to handle variables is determined from the context, some things just doesn't work without doing some preparation.<br />
<br />
I spent several hours figuring out why I couldn't index a character out of a string after doing some math with it just before. The reason was that PHP thought the string was an integer!<br />
<br />
$reference = $base + $userid;<br />
.. looping commands ..<br />
$chartohandle = $reference{$last_char - $i};<br />
<br />
Above doesn't work. Reason: last operation with $reference is to store a product of an addition -&gt; integer variable. $reference .=""; (string catenation) had to be added before I got it to work:<br />
<br />
$reference = $base + $userid;<br />
$reference .= "";<br />
.. looping commands ..<br />
$chartohandle = $reference{$last_char - $i};<br />
<br />
Et voil�! Nice stream of single characters.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.types.string&amp;redirect=@w{GRSENCRS}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types.string&amp;redirect=@w{GRSENCRS}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.types.string.php">show source</a> |
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