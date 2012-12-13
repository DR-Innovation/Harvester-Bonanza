<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
	
	<xsl:template match="/ArrayOfAsset">
		<xsl:for-each select="Asset[AssetId=$AssetId]">
			<xsl:call-template name="asset" />
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="asset">
		<DKA xmlns="http://www.danskkulturarv.dk/DKA.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.danskkulturarv.dk/DKA.xsd ../../Base/schemas/DKA.xsd ">
			<Title><xsl:value-of select="Title"/></Title>
			<Abstract />
			<Description>
				<xsl:value-of select="Description"/>
			</Description>
			<Organization>DR</Organization>
			<Type><xsl:value-of select="AssetType"/></Type>
			<CreatedDate><xsl:value-of select="Created"/></CreatedDate>
			<FirstPublishedDate><xsl:value-of select="FirstPublished"/></FirstPublishedDate>
			<Identifier><xsl:value-of select="AssetId"/></Identifier>
			<xsl:copy-of select="php:function('\CHAOS\Harvester\Bonanza\Processors\AssetXSLTMetadataProcessor::xslt_contributors', string(Actors))"  />
			<xsl:copy-of select="php:function('\CHAOS\Harvester\Bonanza\Processors\AssetXSLTMetadataProcessor::xslt_creators', string(Colophon))" />
			<TechnicalComment />
			<Location />
			<RightsDescription>Copyright © DR</RightsDescription>
			<GeoData><Latitude /><Longitude /></GeoData>
			<Categories>
				<Category><xsl:value-of select="CategoryTitle" /></Category>
			</Categories>
			<Tags>
				<Tag><xsl:value-of select="CategoryTitle" /></Tag>
			</Tags>
			<ProductionID><xsl:value-of select="ProductionId"/></ProductionID>
			<StreamDuration><xsl:value-of select="Duration"/></StreamDuration>
		</DKA>
	</xsl:template>
</xsl:stylesheet>
