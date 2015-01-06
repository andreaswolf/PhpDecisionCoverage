<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml">

	<!-- shamelessly borrowed from phpDox’ class.xsl -->
	<xsl:variable name="unit" select="/*[1]" />

	<xsl:template match="/">
		<html lang="en">
			<head>
				<meta charset="UTF-8" />
				<script type="application/javascript">
					<xsl:text disable-output-escaping="yes"><![CDATA[
					function addClickListener() {
						var sources = document.getElementById('sources');
						sources.addEventListener('click', function(e) {
							if (e.target && e.target.className.split(" ").indexOf("annotated") > 0) {
								var annotations = e.target.getElementsByClassName('annotations');
								annotations[0].style.visibility = "visible";
							}
						});
					}
					]]></xsl:text>
				</script>
				<title>File »<xsl:value-of select="/source/attribute::file" />«</title>
				<style>
					div.line-fragment {
						display:inline;
					}
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
						position:relative;
					}
					.annotations {
						border:1px solid;
						background:#ececec;
						padding:0.2em;
						display: inline-block;
						overflow: hidden;
						position: absolute;
						top: 1.2em;
						z-index: 10;
						visibility:hidden;
					}
				</style>
			</head>
			<body onload="addClickListener();">
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
		<div class="line-fragment">
			<xsl:if test="annotation">
				<xsl:attribute name="class">line-fragment annotated</xsl:attribute>
				<div class="annotations">
					<xsl:for-each select="annotation"><xsl:call-template name="inline-annotation" /></xsl:for-each>
				</div>
			</xsl:if>
			<xsl:choose>
				<xsl:when test="contents"><xsl:value-of select="contents"/></xsl:when>
				<xsl:otherwise><xsl:value-of select="."/></xsl:otherwise>
			</xsl:choose>
		</div>
	</xsl:template>

	<xsl:template name="inline-annotation">
		<xsl:choose>
			<xsl:when test="@type='coverage'">
				<xsl:variable name="coverageId" select="@coverage" />
				<xsl:variable name="inputs-total" select="count(//coverages/coverage[@id=$coverageId]/inputs/input)" />
				<xsl:variable name="inputs-covered" select="count(//coverages/coverage[@id=$coverageId]/inputs/input[@covered='true'])" />
				<xsl:variable name="coverage" select="format-number($inputs-covered div $inputs-total * 100, '0.##')" />
				<span class="inline-annotation"><xsl:value-of select="$coverage" /> %</span>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>