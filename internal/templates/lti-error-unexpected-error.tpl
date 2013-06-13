{assign var=errorTitle value='Unexpected Error'}
{assign var=message value="<p>An unexpected error occured: $errorMessage</p><p>If this problem continues please contact support.</p>"}
{include "$errorTemplatePath"}