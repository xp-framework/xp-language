<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:rss="http://my.netscape.com/rdf/simple/0.9/"
  exclude-result-prefixes="rdf dc rss"
>
  <xsl:output method="xhtml" encoding="iso-8859-1"/>
  <xsl:param name="mode" select="'index'"/>
  <xsl:include href="xsl-helper.xsl"/>

  <xsl:template name="navigation">
    <b>See also</b>
    <br/>
    <ul class="nav">
      <xsl:for-each select="/document/main/references/ref">
        <li><xsl:apply-templates select="."/></li>
      </xsl:for-each>
    </ul>
  </xsl:template>

  <xsl:template match="ref[@type= 'ext']">
    <a title="External link to {@link}" href="{@link}" target="_new"><xsl:value-of select="."/></a>
  </xsl:template>

  <xsl:template match="ref[@type= 'google']">
    <a href="http://google.de/search?q={@link}" target="_new">Google search: <xsl:value-of select="@link"/></a>
  </xsl:template>

  <xsl:template match="ref[@type= 'api:class']">
    <a title="XP API-Doc: {@link}" href="/apidoc/classes/{substring-before(@link, '#')}.html#{substring-after(@link, '#')}"><xsl:value-of select="@link"/></a>
  </xsl:template>
  
  <xsl:template match="ref">
    <a href="/content/{@link}.html"><xsl:value-of select="."/></a>
  </xsl:template>
  
  <xsl:template match="main">
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <th valign="top" align="left"><xsl:value-of select="content/title"/></th>
        <td valign="top" align="right" style="color: #666666">(<xsl:value-of select="content/editor"/>)</td>
	  </tr>
	  <tr bgcolor="#cccccc">
        <td colspan="2"><img src="/image/spacer.gif" height="1" border="0"/>
      </td></tr>
    </table>
    <br/>

    <xsl:choose>
    
      <!-- Multipart: TOC, captions and scrollto links -->
      <xsl:when test="count(content/para) &gt; 1">    
        <table border="0" width="100%" cellspacing="0" cellpadding="2" bgcolor="#f6f6ff" style="border: 1px dotted #3654a5">
          <tr>
            <td width="4%" nowrap="nowrap">
              <img src="/image/nav_toc.gif" height="17" width="17" alt="=&gt;" hspace="2" vspace="2"/>
            </td>
            <td>
              <a name="contents"><b style="color: #000066">
                Table of Contents
              </b></a>
            </td>
          </tr>
          <xsl:for-each select="content/para/caption">
            <tr>
              <td width="4%" nowrap="nowrap">
                <img src="/image/caret-r.gif" height="7" width="11" alt="{position()}" hspace="2" vspace="4"/>
              </td>
              <td>
                <a href="#{position()}"><xsl:value-of select="."/></a>
              </td>
            </tr>
          </xsl:for-each>
        </table>
        <br/>
        <br/>
        <xsl:apply-templates select="content/para"/>
      </xsl:when>
      
      <!-- Single part: No TOC, no scrollto links -->
      <xsl:otherwise>
        <b><xsl:value-of select="content/para/caption"/></b>
        <br/>
        <xsl:apply-templates select="content/para/text"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="main/content/para">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td>
          <a name="{position()}"><b><xsl:value-of select="caption"/></b></a>
        </td>
        <td width="4%" nowrap="nowrap" align="right">
          <a href="#top"><img alt="^" title="Top" border="0" src="/image/nav_top.gif" height="17" width="17" hspace="0" vspace="0"/></a>
        </td>
      </tr>
    </table>
    <xsl:call-template name="divider"/>
    <p style="line-height: 16px; text-align: justify">
      <xsl:apply-templates select="text"/>
    </p>
    <xsl:apply-templates select="advanced"/>
    <br/>
  </xsl:template>

  <xsl:template match="advanced">
    <xsl:call-template name="frame">
      <xsl:with-param name="margin" select="'4'"/>
      <xsl:with-param name="icolor" select="'#eaffea'"/>
      <xsl:with-param name="color" select="'#6a996a'"/>
      <xsl:with-param name="content">
        <span style="line-height: 18px">
          <b>Advanced topics:</b><br/>
          <xsl:for-each select="ref">
            <img src="/image/caret-r.gif" height="7" width="11" alt="{position()}" hspace="2" vspace="0"/> <xsl:apply-templates select="."/><br/>
          </xsl:for-each>
        </span>
      </xsl:with-param>
    </xsl:call-template>
  </xsl:template>
  
  <xsl:template match="code">
    <br/><br/>
    <xsl:call-template name="frame">
      <xsl:with-param name="color" select="'#cccccc'"/>
      <xsl:with-param name="content">
        <code>
          <xsl:apply-templates select="document(concat('php://', .))"/>
        </code>
      </xsl:with-param>
    </xsl:call-template>
    <br/>
  </xsl:template>

  <xsl:template match="text//br|text//tt|php//*">
    <xsl:copy>
      <xsl:copy-of select="@*"/>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>

</xsl:stylesheet>
