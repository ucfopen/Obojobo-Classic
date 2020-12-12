export default aGroup => {
	if (!aGroup.kids) return {}
	// first run through all the the questions and total up the alternates
	// AND normalize questionIndex to always indicate the question index
	// because groups w/o alternates use 0 as their questionIndex
	let curIndex = 0
	const altCounts = []
	const questionIDToCorrectedIndex = {}
	aGroup.kids.forEach(q => {
		// standardize questionIndex
		if (q.questionIndex === 0) {
			curIndex++
			questionIDToCorrectedIndex[q.questionID] = curIndex
		} else {
			questionIDToCorrectedIndex[q.questionID] = q.questionIndex

			if (typeof altCounts[q.questionIndex] === 'undefined') {
				altCounts[q.questionIndex] = 0
				curIndex++
			}

			altCounts[q.questionIndex]++
		}
	})

	// now run through them once more to build objects
	const questionsById = {}
	let currentQuestionIndex = -1
	let currentAltIndex
	aGroup.kids.forEach(q => {
		const correctedIndex = questionIDToCorrectedIndex[q.questionID]

		if (correctedIndex !== currentQuestionIndex) {
			// reset the counters if q.questionIndex changes
			currentQuestionIndex = correctedIndex
			currentAltIndex = 1
		} else {
			// same question as before, increment alt index
			currentAltIndex++
		}

		questionsById[q.questionID] = {
			questionNumber: currentQuestionIndex,
			altNumber: currentAltIndex,
			altTotal: altCounts[correctedIndex],
			type: q.itemType,
			questionItems: q.items,
			originalQuestion: q
		}
	})

	return questionsById
}
