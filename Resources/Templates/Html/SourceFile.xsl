<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml">

	<xsl:template match="/">
		<html lang="en">
			<head>
				<meta charset="UTF-8" />
				<title>File »<xsl:value-of select="/source/attribute::file" />«</title>
				<style>
					.line-number {
						float:left;
						width:3em;
						text-align:right;
						font-size:0.55em;
						font-family:Arial, Helvetica, sans-serif;
						vertical-align:bottom;
					}
					.line-contents {
						padding-left:3.5em;
						font-family:monospace;
						height:1.2em;
						white-space:pre;
						border-bottom:1px solid #eee;
					}
				</style>
			</head>
			<body>
				<div id="sources">
					<xsl:apply-templates select="/source/lines" />
				</div>
			</body>
		</html>
	</xsl:template>

	<xsl:template name="sourceline" match="lines/line">
		<div class="source-line">
			<div class="line-number"><xsl:value-of select="attribute::number" /></div>
			<div class="line-contents"><xsl:call-template name="line-contents" /></div>
		</div>
	</xsl:template>

	<xsl:template name="line-contents">
		<xsl:choose>
			<xsl:when test="fragment">
				<xsl:for-each select="fragment"><xsl:call-template name="line-fragment" /></xsl:for-each>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="."/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="line-fragment">
		<span class="line-fragment"><xsl:value-of select="."/></span>
	</xsl:template>
</xsl:stylesheet>