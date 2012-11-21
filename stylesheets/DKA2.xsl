<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<xsl:template match="/ArrayOfAsset">
		<xsl:for-each select="Asset[AssetId=$AssetId]">
			<xsl:call-template name="asset" />
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="asset">
		<DKA xmlns="http://www.danskkulturarv.dk/DKA2.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.danskkulturarv.dk/DKA2.xsd ../schemas/DKA2.xsd ">
			<Title><xsl:value-of select="Title"/></Title>
			<Abstract />
			<Description>
				<xsl:value-of select="Description"/>
			</Description>
			<Organization>DR</Organization>
			<ExternalURL>http://www.dr.dk/bonanza/search.htm?needle=<xsl:value-of select="Title"/></ExternalURL>
			<ExternalIdentifier><xsl:value-of select="AssetId"/></ExternalIdentifier>
			<Type><xsl:value-of select="AssetType"/></Type>
			<!-- Contributors>
			</Contributors>
			<Creators>
				<xsl:for-each select="administrator">
					<Creator>
						<xsl:attribute name="Name"><xsl:value-of select="name" /></xsl:attribute>
						<xsl:attribute name="Role">Administrator</xsl:attribute>
					</Creator>
				</xsl:for-each>
				<xsl:for-each select="images/image[count(. | key('image-by-credit', credit)[1]) = 1 and credit != '']">
					<xsl:sort select="credit" />
					<Creator>
						<xsl:attribute name="Name"><xsl:value-of select="credit" /></xsl:attribute>
						<xsl:attribute name="Role">Fotograf</xsl:attribute>
					</Creator>
				</xsl:for-each>
				<xsl:for-each select="stories/story[count(. | key('story-by-author_name', author/name)[1]) = 1 and author/name != '']">
					<xsl:sort select="author/name" />
					<Creator>
						<xsl:attribute name="Name"><xsl:value-of select="author/name" /></xsl:attribute>
						<xsl:attribute name="Role">Forfatter</xsl:attribute>
					</Creator>
				</xsl:for-each>
			</Creators>
			<TechnicalComment />
			<Location><xsl:value-of select="geography/municipality"/></Location>
			<RightsDescription>Copyright © Kulturstyrelsen (<xsl:value-of select="@license"/>)</RightsDescription>
			<Categories/>
			<Tags>
				<xsl:for-each select="themes/theme">
					<Tag><xsl:value-of select="title"/></Tag>
				</xsl:for-each>
				<xsl:for-each select="tags/tag">
					<Tag><xsl:value-of select="value"/></Tag>
				</xsl:for-each>
			</Tags-->
		</DKA>
	</xsl:template>
</xsl:stylesheet>