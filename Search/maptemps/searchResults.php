<?php
<?xml version="1.0" encoding="utf-8"?>
echo "
<kml xmlns="http://www.opengis.net/kml/2.2">
	<Document>
		<Placemark>
			<name>Results for ".$address."</name>
			<description>
				<![CData[
				<h1>I'll Play with this later</h1>
				]]>				
			</description>
			<Point>
				<coordinates>".$gpsX,$gpsY."</coordinates>
			</Point>
		</Placemark>
	</Document>
</kml>";
?>