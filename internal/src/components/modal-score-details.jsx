import './modal-score-details.scss'
import React, { useState } from 'react'
import PropTypes from 'prop-types'
import DataGridStudentScores from './data-grid-student-scores'
import QuestionPreview from './question-preview'
import InstructionsFlag from './instructions-flag'
import AttemptDetails from './attempt-details'
import getProcessedQuestionData from '../util/get-processed-question-data'

const getResponseDataByAttempt = (questions, questionsByID, attemptLogs) => {
	const responseDataByAttempt = attemptLogs.map(attemptLog => {
		const responseDataForAttempt = {
			attempt: attemptLog,
			data: []
		}

		// If alternates are enabled then qOrder is a comma-separated list of question IDs, representing
		// the order of questions shown. Otherwise it's an empty-string, in which case we assume the
		// natural order of questions
		const questionOrder = attemptLog.attempt.qOrder
			? attemptLog.attempt.qOrder.split(',')
			: questions.map(q => q.questionID)
		questionOrder.forEach(questionID => {
			const scoreLogForQuestion = attemptLog.scores.find(scoreLog => scoreLog.itemID === questionID)

			if (!scoreLogForQuestion) {
				return
			}

			const question = questionsByID[questionID]
			responseDataForAttempt.data.push({
				...question,
				score: scoreLogForQuestion.score,
				answer: scoreLogForQuestion.answer
			})
		})

		return responseDataForAttempt
	})

	return responseDataByAttempt
}

export default function ModalScoreDetails({ questions, attemptLogs, userName }) {
	const [questionsByID, numTotalQuestions] = getProcessedQuestionData(questions)

	const responseDataByAttempt = getResponseDataByAttempt(questions, questionsByID, attemptLogs)

	const [selectedItem, setSelectedItem] = useState({})

	const renderPreview = () => {
		switch (selectedItem.type) {
			case 'question': {
				return <QuestionPreview question={selectedItem.question} response={selectedItem.response} />
			}

			case 'attempt': {
				return (
					<AttemptDetails
						attemptNumber={selectedItem.attemptIndex + 1}
						score={selectedItem.attempt.score}
						numAnsweredQuestions={selectedItem.scores.length}
						numTotalQuestions={numTotalQuestions}
						startTime={selectedItem.attempt.startTime}
						endTime={selectedItem.attempt.endTime}
					/>
				)
			}

			default:
				return (
					<div className="instructions">
						<InstructionsFlag text="Select an attempt row to see details about that attempt" />
						<InstructionsFlag text="Select a question to see how the student answered" />
					</div>
				)
		}
	}

	return (
		<div className="modal-score-details">
			<div className="left-pane">
				<div className="modal-title">
					<h3>Student Score Details</h3>
					<p>{userName}</p>
				</div>

				<div className="student-scores">
					{responseDataByAttempt.map((responseDataForAttempt, attemptIndex) => {
						return (
							<div key={attemptIndex}>
								<div
									className={`attempt-row ${
										selectedItem &&
										selectedItem.type === 'attempt' &&
										selectedItem.attemptIndex === attemptIndex
											? 'is-selected'
											: 'is-not-selected'
									}`}
									onClick={() => {
										setSelectedItem({
											type: 'attempt',
											attemptIndex,
											...responseDataForAttempt.attempt
										})
									}}
								>
									<h4>{`Attempt ${attemptIndex + 1}: ${
										responseDataForAttempt.attempt.attempt.score
									}%`}</h4>
									<span>{`Answered questions: ${responseDataForAttempt.data.length}/${numTotalQuestions}`}</span>
								</div>
								<DataGridStudentScores
									selectedIndex={
										selectedItem &&
										selectedItem.type === 'question' &&
										selectedItem.attemptIndex === attemptIndex
											? selectedItem.itemIndex
											: null
									}
									data={responseDataForAttempt.data}
									onSelect={index => {
										setSelectedItem({
											type: 'question',
											attemptIndex,
											itemIndex: index,
											question: responseDataForAttempt.data[index].originalQuestion,
											response: responseDataForAttempt.data[index].answer
										})
									}}
								/>
							</div>
						)
					})}
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
			questionID: PropTypes.string,
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
							mediaID: PropTypes.number,
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
				attemptID: PropTypes.string.isRequired,
				score: PropTypes.string.isRequired,
				startTime: PropTypes.string.isRequired,
				endTime: PropTypes.string.isRequired,
				qOrder: PropTypes.string.isRequired
			}),
			scores: PropTypes.arrayOf(
				PropTypes.shape({
					itemID: PropTypes.string.isRequired,
					answerID: PropTypes.string.isRequired,
					answer: PropTypes.string.isRequired,
					score: PropTypes.string.isRequired
				})
			)
		})
	)
}
