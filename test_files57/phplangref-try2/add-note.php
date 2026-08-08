<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head>
 <title>PHP: Add Manual Note</title>
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
 <link rel="canonical" href="http://php.net/manual/add-note.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/add-note.php" />
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
  </p>
 </form>
</div>

<div id="layout_1">
 <div id="content" class="manual">
<p>
 <center>
  <h2>Pay Attention Now!</h2>
  <img src="http://imgs.xkcd.com/comics/freedom.png" title="This is how we feel sometimes when obvious violations take place here!"/><br/>
  <b>NOTE:</b> Due to the overwhelming lack of folks who seem to notice
  that there are guidelines for what should <i>not</i> be posted here,
  resulting in the need for us to moderate thousands of submissions,
  consider this our form of grabbing your attention.  BEFORE YOU POST,
  <a href="#whatnottoenter">READ THIS SECTION</a>, PLEASE!
 </center>
</p>

<h3>Welcome to the PHP Manual user note system</h3>
<p>
 You may contribute notes to the PHP manual by adding comments in the
 form below, and, optionally your email address and/or name. And the
 note will appear under the documentation as a part of the manual after
 about an hour.
</p>

<h3>How to enter information</h3>
<p>
 There is no need to obfuscate your email address, as we have a simple
 conversion in place to convert the @ signs and dots in your address. You
 may still want to include a part in the email address only understandable
 by humans, to make it spam protected, as our conversion can be performed
 the other way too. You may submit your email address as
 <tt>user@NOSPAM.example.com</tt> for example (which will be displayed
 as <tt>user at NOSPAM dot example dot com</tt>. Although note that we can
 only inform you of the removal of your note, if you use your real email
 address.
</p>
<p>
 Note that HTML tags are not allowed in the posts, but the note formatting
 is preserved. URLs will be turned into clickable links, PHP code blocks
 enclosed in the PHP tags &lt;?php and ?&gt; will
 be source highlighted automatically. So always enclose PHP snippets in
 these tags. <em>(Double-check that your note appears
 as you want during the preview. That's why it is there!)</em>
</p>
<p>
 The SPAM challenge requires numbers to written out in English, so, an appropriate
 answer may be <em>nine</em> but not <em>9</em>.
</p>

<a name="whatnottoenter"><h3>What not to enter</h3></a>
<p>
 User notes may be edited or deleted, and usually a note is deleted 
 because of the following reasons:
</p>
<ul>
 <li>
  <strong>Bugs</strong>. Instead
  <a href="http://bugs.php.net/report.php?bug_type=Documentation+problem&amp;manpage=langref">report a bug</a>
  for this manual page to the bug database.
 </li>
 <li>
  <strong>Missing documentation</strong>. Also, report that as a bug.
 </li>
 <li>
  <strong>Support questions</strong>. See the <a href="/support.php">support page</a>
  for available options. In other words, do not ask questions within the user notes.
 </li>
 <li>
  <strong>References to other notes or authors</strong>.  This is not a forum, so we
  neither encourage nor permit discussions here.  Further, if a note is referenced
  directly, and that note is later removed or modified, it can cause confusion.
 </li>
 <li>
  <strong>Code collaboration or improvements</strong>. This is not to suggest that
  your code modification is not good, perhaps even great, but this just isn't the
  place to show it off.  We don't even accept all original code submissions.  Your
  best bet is to publish it on your blog or via another medium.
 </li>
 <li>
  <strong>Links to your website, blog, code, or a third-party website</strong>. We do,
  on occasion, permit the posting of famous websites (such as faqs.org or the MySQL
  manual), but links to other sites, no matter how well-intended, will likely be removed.
 </li>
 <li>
  <strong>Complaints that your notes keep getting deleted</strong>. Sometimes the content
  of your note may be fine, but we might just hate your face for no good reason. (More
  likely, though, you didn't bother to read this page, and you violated one of these
  rules.)
 </li>
 <li>
  <strong>Notes in languages other than English</strong>. 不 gach duine понимает
  el lenguaje जिसमें Sie sprechen.
 </li>
 <li>
  <strong>SPAM</strong>. This should go without saying, but apparently some folks
  out there just don't get it.
 </li>
 <li>
  <strong>Your disdain for PHP and/or its maintainers</strong>. Go learn FORTRAN instead.
 </li>
</ul>

<h3>Additional information</h3>
<p>
 Please note that periodically the developers go through the notes and
 may incorporate information from them into the documentation. This means
 that any note submitted here becomes the property of the PHP Documentation
 Group and will be available under the <a href="/license/index.php#doc-lic">same license</a> as the documentation.
</p>
<p>
 Your IP Address will be logged with the submitted note and made public on the
 PHP manual user notes mailing list. The IP address is logged as part of the
 notes moderation process, and won't be shown within the PHP manual itself.
</p>
<form method="post" action="/manual/add-note.php">
 <p>
  <input type="hidden" name="sect" value="langref" />
  <input type="hidden" name="redirect" value="http://www.php.net/manual/en/langref.php" />
 </p>
 <table border="0" cellpadding="3" class="standard">
  <tr>
   <td colspan="2">
    <b>
     <a href="/support.php">Click here to go to the support pages.</a><br />
     <a href="http://bugs.php.net/report.php?bug_type=Documentation+problem&amp;manpage=langref">Click here to submit a bug report.</a><br />
     <a href="http://bugs.php.net/report.php?bug_type=Documentation+problem&amp;manpage=langref">Click here to request a feature.</a><br />
     (Again, please note, if you ask a question, report a bug, or request a feature,
     your note <i>will be deleted</i>.)
    </b>
   </td>
  </tr>
  <tr>
   <th class="subr">Your email address (or name):</th>
   <td><input type="text" name="user" size="60" maxlength="40" value="user@example.com" /></td>
  </tr>
  <tr>
   <th class="subr">Your notes:</th>
   <td><textarea name="note" rows="20" cols="60" wrap="virtual"></textarea>
   <br />
  </td>
  </tr>
  <tr>
   <th class="subr">Answer to this simple question (SPAM challenge):<br />
   two plus three?</th>
   <td><input type="text" name="answer" size="60" maxlength="10" /> (Example: nine)</td>
  </td>
  </tr>
  <tr>
   <th colspan="2">
    <input type="hidden" name="func" value="plus" />
    <input type="hidden" name="arga" value="two" />
    <input type="hidden" name="argb" value="three" />
    <input type="submit" name="action" value="Preview" />
    <input type="submit" name="action" value="Add Note" />
   </th>
  </tr>
 </table>
</form>

 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/add-note.php">show source</a> |
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