const patternTF = /<\/?textformat\s?[\s\S]*?>/gi
const patternPFont = /<\s*p(\s+align=(?:"|')(left|right|center|justify)(?:"|'))?\s*><\s*font(\s+[\s\S]*?=(?:"|')[\s\S]*?(?:"|'))\s*>/gi
const patternPFontClose = /<\/font><\/p>/gi
const patternFont = /<font[\s\S]*?>/gi
const patternFontClose = /<\/font>/gi
const patternEmpty1 = /<(\w+?)[^>]*?>(\s*?)<\/\1>/gi
const patternEmpty2 = /<(\w+)>(\s*?)<\/\1>/gi
const patternRemoveUL = /<\/?ul>/gi
const patternAddUL = /<LI>([\s\S]*?)<\/LI>/gi
const patternRemoveExtraUL = /<\/ul><ul>/gi

// Old learning objects were saved using flash's textfields - which suck at html
const cleanFlashHTML = function(input) {
	// get rid of all the textformat tags
	input = input.replace(patternTF, '')

	// combine <p><font>...</font></p> tags to just <p></p>
	// input = input.replace(pattern, '<p style="font-family:$2;font-size:$3px;color:$4;">');
	//input = input.replace(patternPFont, '<p>');
	let matchFound = true
	let groups
	let lastIndex
	while (matchFound) {
		patternPFont.lastIndex = 0
		groups = patternPFont.exec(input)
		lastIndex = patternPFont.lastIndex
		if (groups && groups.length >= 0) {
			if (groups.length >= 3) {
				const align = groups[2].toLowerCase()
				//input = input.replace(patternPFont, '<p style="text-align:' + align + ';">');
				input =
					input
						.substr(0, lastIndex)
						.replace(patternPFont, '<p style="text-align:' + align + ';">') +
					input.substr(lastIndex)
			} else {
				input = input.substr(0, lastIndex).replace(patternPFont, '<p>') + input.substr(lastIndex)
			}
		} else {
			matchFound = false
		}
	}

	input = input.replace(patternPFontClose, '</p>')
	// convert lone <font>...</font> tags to spans
	input = input.replace(patternFont, '<span>')

	input = input.replace(patternFontClose, '</span>')
	// find empty tags keeping space in them
	// we loop here to help transform nested empty tags such as "<li><b></b></li>"
	matchFound = true
	while (matchFound) {
		patternEmpty1.lastIndex = 0
		groups = patternEmpty1.exec(input)
		if (groups && groups.length >= 2) {
			input = input.replace(patternEmpty1, '$2')
		} else {
			matchFound = false
		}
	}

	matchFound = true
	while (matchFound) {
		patternEmpty2.lastIndex = 0
		groups = patternEmpty2.exec(input)
		if (groups && groups.length >= 2) {
			input = input.replace(patternEmpty2, '$2')
		} else {
			matchFound = false
		}
	}

	// remove any previously added ul tags
	input = input.replace(patternRemoveUL, '')

	// add <ul></ul> around list items
	input = input.replace(patternAddUL, '<ul><li>$1</li></ul>') // @TODO DOES THIS WORK??????????

	// kill extra </ul><ul> that are back to back - this will make proper lists
	input = input.replace(patternRemoveExtraUL, '')

	// input = createOMLTags(input)

	return input
}

export default cleanFlashHTML
