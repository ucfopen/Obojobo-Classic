export default questions => {
	const questionsById = {}
	let lastQuestionIndex = -1
	let questionCounter = 0
	let altCounter = 0
	let numTotalQuestions = 0
	const questionsWhichNeedATotalAltCounterUpdate = []

	questions.forEach(q => {
		if (q.questionIndex === 0 || lastQuestionIndex !== questionCounter) {
			questionsWhichNeedATotalAltCounterUpdate.forEach(q => {
				q.questionNumber.totalAlts = questionsWhichNeedATotalAltCounterUpdate.length
			})
			questionsWhichNeedATotalAltCounterUpdate.length = 0

			questionCounter++
			numTotalQuestions++
			altCounter = 0
		} else {
			altCounter++
		}

		questionsById[q.questionID] = {
			questionNumber: {
				displayNumber: questionCounter,
				altNumber: altCounter + 1,
				totalAlts: 0
			},
			type: q.itemType,
			questionItems: q.items,
			originalQuestion: q
		}

		if (q.questionIndex !== 0) {
			questionsWhichNeedATotalAltCounterUpdate.push(questionsById[q.questionID])
		}

		lastQuestionIndex = q.questionIndex
	})

	questionsWhichNeedATotalAltCounterUpdate.forEach(q => {
		q.questionNumber.totalAlts = questionsWhichNeedATotalAltCounterUpdate.length
	})

	return [questionsById, numTotalQuestions]
}
