import './modal-score-details.scss'
import React from 'react'
import PropTypes from 'prop-types'
import DataGridStudentScores from './data-grid-student-scores'
import QuestionPreview from './question-preview'
import InstructionsFlag from './instructions-flag'
import AttemptDetails from './attempt-details'
import getProcessedQuestionData from '../util/get-processed-question-data'

const getAnsweredQuestions = (questionsByID, attemptLogs) => {
	const answeredQuestions = []
	attemptLogs.forEach((attemptLog, index) => {
		attemptLog.attempt.questionOrder.forEach(questionID => {
			const scoreLogForQuestion = attemptLog.scores.find(s => s.itemID === questionID)

			if (!scoreLogForQuestion) {
				return
			}

			const question = questionsByID[questionID]
			answeredQuestions.push({
				...question,
				questionID: question.originalQuestion.questionID,
				score: scoreLogForQuestion.score,
				answer: scoreLogForQuestion.answer,
				attempt: attemptLog.attempt,
				attemptIndex: index+1,
				answersSavedForAttempt: attemptLog.scores.length
			})
		})
	})

	// return the list, sorted by attemptIndex, questionNumber
	return answeredQuestions.sort((a, b) => {
		const attemptOrder = a.attemptIndex - b.attemptIndex
		if(attemptOrder !== 0) return attemptOrder
		return a.questionNumber - b.questionNumber
	})
}

export default function ModalScoreDetails({ aGroup, attemptLogs, userName }) {
	const questionsByID = React.useMemo(() => getProcessedQuestionData(aGroup), [aGroup])
	const answeredQuestions = React.useMemo(() => getAnsweredQuestions(questionsByID, attemptLogs), [aGroup, attemptLogs, userName])
	const [selectedItem, setSelectedItem] = React.useState()

	const renderPreview = () => {
		if(!selectedItem){
			return (
				<div className="instructions">
					<InstructionsFlag text="Select an attempt row to see details about that attempt" />
					<InstructionsFlag text="Select a question to see how the student answered" />
				</div>
			)
		}

		return (
			<div>
				<AttemptDetails
					attemptNumber={selectedItem.attemptIndex }
					score={selectedItem.attempt.score}
					numAnsweredQuestions={selectedItem.answersSavedForAttempt}
					numTotalQuestions={aGroup.quizSize}
					startTime={selectedItem.attempt.startTime}
					endTime={selectedItem.attempt.endTime}
				/>
				<hr/>
				<QuestionPreview question={selectedItem.originalQuestion} score={selectedItem.score} response={selectedItem.answer} />
			</div>
		)
	}

	return (
		<div className="modal-score-details">
			<div className="left-pane">
				<div className="modal-title">
					<h3>Student Score Details</h3>
					<p>{userName}</p>
				</div>

				<div className="student-scores">
						<DataGridStudentScores
							data={answeredQuestions}
							onSelect={row => {
								setSelectedItem(row)
							}}
						/>
				</div>
			</div>
			<div className="right-pane">{renderPreview()}</div>
		</div>
	)
}

ModalScoreDetails.propTypes = {
	// userID: PropTypes.string.isRequired,
	// instID: PropTypes.string.isRequired,
	// loID: PropTypes.string.isRequired
	userName: PropTypes.string.isRequired,
	questions: PropTypes.arrayOf(
		PropTypes.shape({
			questionID: PropTypes.number,
			itemType: PropTypes.oneOf(['MC', 'QA', 'Media']),
			answers: PropTypes.arrayOf(
				PropTypes.shape({
					answerID: PropTypes.string,
					answer: PropTypes.string,
					weight: PropTypes.number
				})
			),
			items: PropTypes.arrayOf(
				PropTypes.shape({
					component: PropTypes.oneOf(['TextArea', 'MediaView']),
					data: PropTypes.string,
					media: PropTypes.arrayOf(
						PropTypes.shape({
							mediaID: PropTypes.string,
							title: PropTypes.string,
							itemType: PropTypes.oneOf(['pic', 'kogneato', 'swf', 'flv', 'mp3', 'youTube']),
							descText: PropTypes.string,
							width: PropTypes.number,
							height: PropTypes.number
						})
					)
				})
			)
		})
	),
	attemptLogs: PropTypes.arrayOf(
		PropTypes.shape({
			attempt: PropTypes.shape({
				attemptID: PropTypes.number.isRequired,
				score: PropTypes.number.isRequired,
				startTime: PropTypes.number.isRequired,
				endTime: PropTypes.number.isRequired,
				qOrder: PropTypes.string.isRequired
			}),
			scores: PropTypes.arrayOf(
				PropTypes.shape({
					itemID: PropTypes.number.isRequired,
					answerID: PropTypes.number.isRequired,
					answer: PropTypes.string.isRequired,
					score: PropTypes.number.isRequired
				})
			)
		})
	)
}
