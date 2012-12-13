<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
	
	<xsl:template match="/ArrayOfAsset">
		<xsl:for-each select="Asset[AssetId=$AssetId]">
			<xsl:call-template name="asset" />
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="asset">
		<DKA xmlns="http://www.danskkulturarv.dk/DKA2.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.danskkulturarv.dk/DKA2.xsd ../../Base/schemas/DKA2.xsd ">
			<Title><xsl:value-of select="Title"/></Title>
			<Abstract />
			<Description>
				<xsl:value-of select="Description"/>
				<xsl:variable name="actors_for_description" select="php:function('\CHAOS\Harvester\Bonanza\Processors\AssetXSLTMetadataProcessor::xslt_actors_for_description', string(Actors))" />
				<xsl:if test="$actors_for_description != ''">
					<p xmlns="http://www.w3.org/1999/xhtml" class="actors">
					<xsl:value-of select="$actors_for_description" />
					</p>
				</xsl:if>
				<xsl:variable name="colophon_for_description" select="php:function('\CHAOS\Harvester\Bonanza\Processors\AssetXSLTMetadataProcessor::xslt_colophon_for_description', string(Colophon))" />
				<xsl:if test="$colophon_for_description != ''">
					<p xmlns="http://www.w3.org/1999/xhtml" class="colophon">
					<xsl:value-of select="$colophon_for_description" />
					</p>
				</xsl:if>
			</Description>
			<Organization>DR</Organization>
			<ExternalURL>http://www.dr.dk/bonanza/search.htm?needle=<xsl:value-of select="Title"/></ExternalURL>
			<ExternalIdentifier><xsl:value-of select="AssetId"/></ExternalIdentifier>
			<Type><xsl:value-of select="AssetType"/></Type>
			<CreatedDate><xsl:value-of select="Created"/></CreatedDate>
			<FirstPublishedDate><xsl:value-of select="FirstPublished"/></FirstPublishedDate>
			<xsl:copy-of select="php:function('\CHAOS\Harvester\Bonanza\Processors\AssetXSLTMetadataProcessor::xslt_contributors_2', string(Actors))" />
			<xsl:copy-of select="php:function('\CHAOS\Harvester\Bonanza\Processors\AssetXSLTMetadataProcessor::xslt_creators_2', string(Colophon))" />
			<RightsDescription>Copyright © DR</RightsDescription>
			<Categories>
				<Category><xsl:value-of select="CategoryTitle" /></Category>
			</Categories>
			<Tags>
				<Tag><xsl:value-of select="CategoryTitle" /></Tag>
			</Tags>
			<Metafield>
				<Key>ProductionId</Key>
				<Value><xsl:value-of select="ProductionId"/></Value>
			</Metafield>
			<Metafield>
				<Key>Duration</Key>
				<Value><xsl:value-of select="Duration"/></Value>
			</Metafield>
		</DKA>
	</xsl:template>
</xsl:stylesheet>
