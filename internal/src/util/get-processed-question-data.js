export default aGroup => {
	// first run through all the the questions and total up the alternates
	// AND normalize questionIndex to always indicate the question index
	// because groups w/o alternates use 0 as their questionIndex
	const altCounts = []
	aGroup.kids.forEach((q, index) => {
		// standardize questionIndex
		if (q.questionIndex === 0) q.questionIndex = index + 1
		// have we seen this index yet?
		if (typeof altCounts[q.questionIndex] === 'undefined') altCounts[q.questionIndex] = 1
		// increment how many alts there are for this index
		else altCounts[q.questionIndex] = altCounts[q.questionIndex] + 1
	})

	// now run through them once more to build objects
	const questionsById = {}
	let currentQuestionIndex = -1
	let currentAltIndex
	aGroup.kids.forEach(q => {
		if (q.questionIndex !== currentQuestionIndex) {
			// reset the counters if q.questionInex changes
			currentQuestionIndex = q.questionIndex
			currentAltIndex = 1
		} else {
			// same question as before, increment alt index
			currentAltIndex++
		}

		questionsById[q.questionID] = {
			questionNumber: currentQuestionIndex,
			altNumber: currentAltIndex,
			altTotal: altCounts[q.questionIndex],
			type: q.itemType,
			questionItems: q.items,
			originalQuestion: q
		}
	})

	return questionsById
}
