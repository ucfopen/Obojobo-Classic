<!DOCTYPE html>
<html>
	<head>
		<title>Obojobo Test as Provider</title>
		<meta charset="utf-8" />
		<script type="text/javascript"></script>
		<style type="text/css"></style>
	</head>
	<body>
		<header>
			<h1>Use Obojobo as an LTI Provider (inserted into another system)</h1>
		</header>
		<section>
			<p>This page will act as an LMS sending an LTI request to Obojobo.</p>
			<div>
				<label><input onclick="toggleVariableWidthIFrame()" type="checkbox" id="variable_iframe" checked />Variable width iframe</label>
			</div>

			<iframe style="border: 1px solid black;" name="embed_iframe" id="embed_iframe" width="700px" height="600px"></iframe>
			<script type="text/javascript">
				function toggleVariableWidthIFrame()
				{
					var iframe = document.getElementById('embed_iframe');
					if(typeof __iframeInitWidth === 'undefined')
					{
						__iframeInitWidth = iframe.width;
					}
					var variableWidth = document.getElementById('variable_iframe').checked;
					iframe.width = variableWidth ? '100%' : __iframeInitWidth;
				}

				toggleVariableWidthIFrame();
			</script>

			<form method="POST" target="embed_iframe" action="{$instructorEndpoint}" >
				{foreach from=$instructorParams key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
				{/foreach}
				<input type="submit" value="As Instructor">
			</form>

			<form method="POST" target="embed_iframe" action="{$instructorNewEndpoint}" >
				{foreach from=$instructorNewParams key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
				{/foreach}
				<input type="submit" value="As NEW Instructor">
			</form>

			<form method="POST" target="embed_iframe" action="{$instructorAssignmentEndpoint}" >
				{foreach from=$instructorAssignmentParams key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
				{/foreach}
				<input type="submit" value="As Instructor (Assignment)">
			</form>

			<form method="POST" target="embed_iframe" action="{$learnerEndpoint}" >
				{foreach from=$learnerParams key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
				{/foreach}
				<input type="submit" value="As Learner">
			</form>

			<form method="POST" target="embed_iframe" action="{$learnerNewEndpoint}" >
				{foreach from=$learnerNewParams key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
				{/foreach}
				<input type="submit" value="As NEW Learner">
			</form>

			<form method="POST" target="embed_iframe" action="{$learnerPickerEndpoint}" >
				{foreach from=$learnerPickerParams key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
				{/foreach}
				<input type="submit" value="As Learner (Picker)">
			</form>

			<form method="POST" target="embed_iframe" action="{$unknownRoleEndpoint}" >
				{foreach from=$unknownRoleParams key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
				{/foreach}
				<input type="submit" value="As Unknown Role">
			</form>

			<form method="POST" target="embed_iframe" action="{$unknownAssignmentEndpoint}" >
				{foreach from=$unknownAssignmentParams key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
				{/foreach}
				<input type="submit" value="As Unknown Assignment">
			</form>

			<form method="POST" target="embed_iframe" action="{$unknownUserEndpoint}" >
				{foreach from=$unknownUserParams key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
				{/foreach}
				<input type="submit" value="As Unknown User">
			</form>

			<form method="POST" target="embed_iframe" action="{$testUserEndpoint}" >
				{foreach from=$testUserParams key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
				{/foreach}
				<input type="submit" value="As Test User">
			</form>

			<form method="POST" target="embed_iframe" action="{$pickerRedirectEndpoint}" >
				{foreach from=$pickerRedirectParams key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
				{/foreach}
				<input type="submit" value="Picker redirect">
			</form>

			<form method="POST" target="embed_iframe" action="{$copiedPickerEndpoint}" >
				{foreach from=$copiedPickerParams key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
				{/foreach}
				<input type="submit" value="Copied Picker">
			</form>

			<form method="POST" target="embed_iframe" action="{$validationEndpoint}" >
				{foreach from=$validationParams key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
				{/foreach}
				<input type="submit" value="Validation">
			</form>

			<hr/>
		</section>
	</body>
</html>