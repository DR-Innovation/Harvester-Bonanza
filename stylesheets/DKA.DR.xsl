<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
	
	<xsl:template match="/ArrayOfAsset">
		<xsl:for-each select="Asset[AssetId=$AssetId]">
			<xsl:call-template name="asset" />
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="asset">
		<DR xmlns="http://www.danskkulturarv.dk/DKA.DR.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.danskkulturarv.dk/DKA.DR.xsd ../../../schemas/DKA.DR.xsd ">
			<ProductionID><xsl:value-of select="ProductionId"/></ProductionID>
			<StreamDuration><xsl:value-of select="Duration"/></StreamDuration>
		</DR>
	</xsl:template>
</xsl:stylesheet>