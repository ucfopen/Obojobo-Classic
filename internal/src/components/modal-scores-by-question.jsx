import './modal-scores-by-question.scss'

import React from 'react'
import PropTypes from 'prop-types'
import QuestionScoreDetails from './question-score-details'
import DataGridStudentScores from './data-grid-student-scores'
import getProcessedQuestionData from '../util/get-processed-question-data'

const getScoreDataByQuestionID = submitQuestionLogsByUser => {
	const logDataByQuestionID = {}

	for (const userID in submitQuestionLogsByUser) {
		const submitQuestionLogsForUser = submitQuestionLogsByUser[userID]
		submitQuestionLogsForUser.logs.forEach(log => {
			if (!logDataByQuestionID[log.valueA]) {
				logDataByQuestionID[log.valueA] = {
					logs: [],
					totalScore: 0,
					answers: []
				}
			}

			const logByQuestion = logDataByQuestionID[log.valueA]

			logByQuestion.logs.push(log)
			logByQuestion.totalScore += parseFloat(log.score)
			logByQuestion.answers.push({
				userName: submitQuestionLogsForUser.userName,
				response:
					log.answerIndex === '?' ? log.valueB : String.fromCharCode(log.answerIndex - 1 + 65),
				score: parseInt(log.score, 10),
				time: parseInt(log.createTime, 10)
			})
		})
	}

	return logDataByQuestionID
}

export default function ModalScoresByQuestion({ aGroup, submitQuestionLogsByUser }) {
	const [selectedItem, setSelectedItem] = React.useState()
	const questionsByID = React.useMemo(() => getProcessedQuestionData(aGroup), [aGroup])

	const scoreDataByQuestionID = React.useMemo(
		() => getScoreDataByQuestionID(submitQuestionLogsByUser),
		[submitQuestionLogsByUser]
	)

	const data = React.useMemo(() => {
		return aGroup.kids.map(q => {
			const scoreData = scoreDataByQuestionID[q.questionID]

			return {
				...questionsByID[q.questionID],
				responses: scoreData ? scoreData.answers : [],
				score:
					!scoreData || scoreData.answers.length === 0
						? null
						: scoreData.totalScore / scoreData.answers.length
			}
		})
	}, [aGroup, submitQuestionLogsByUser])

	return (
		<div className="modal-scores-by-question">
			<div className="scores-by-question--left-sidebar">
				<h2>Scores By Question</h2>
				<div className="wrapper">
					<DataGridStudentScores
						showAttemptColumn={false}
						data={data}
						onSelect={row => {
							setSelectedItem(row)
						}}
					/>
				</div>
			</div>

			<div className="score-details-right-content">
				{selectedItem ? (
					<QuestionScoreDetails
						questionNumber={selectedItem.questionNumber}
						altNumber={selectedItem.altNumber}
						question={selectedItem.originalQuestion}
						responses={selectedItem.responses}
					/>
				) : null}
			</div>
		</div>
	)
}

ModalScoresByQuestion.propTypes = {
	aGroup: PropTypes.arrayOf(
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
							itemType: PropTypes.oneOf(['pic', 'kogneato', 'swf', 'flv', 'youTube']),
							descText: PropTypes.string,
							width: PropTypes.number,
							height: PropTypes.number
						})
					)
				})
			)
		})
	),
	submitQuestionLogsByUser: PropTypes.arrayOf(
		PropTypes.shape({
			userName: PropTypes.string.isRequired,
			logs: PropTypes.arrayOf(
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
