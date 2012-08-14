<?xml version="1.0" encoding="UTF-8"?>
<imsx_POXEnvelopeResponse xmlns = "http://www.imsglobal.org/lis/oms1p0/pox">
	<imsx_POXHeader>
	<imsx_POXResponseHeaderInfo>
		<imsx_version>V1.0</imsx_version>
		<imsx_messageIdentifier>{$messsageIdOut}</imsx_messageIdentifier>
		<imsx_statusInfo>
			<imsx_codeMajor>{$success}</imsx_codeMajor>
			<imsx_severity>status</imsx_severity>
			<imsx_description>{$description}</imsx_description>
			<imsx_messageRefIdentifier>{$messsageIdIn}</imsx_messageRefIdentifier>
			<imsx_operationRefIdentifier>replaceResult</imsx_operationRefIdentifier>
		</imsx_statusInfo>
		</imsx_POXResponseHeaderInfo>
	</imsx_POXHeader>
	<imsx_POXBody>
		<replaceResultResponse/>
	</imsx_POXBody>
</imsx_POXEnvelopeResponse>