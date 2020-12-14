import './modal-scores-by-question.scss'

import React from 'react'
import PropTypes from 'prop-types'
import QuestionScoreDetails from './question-score-details'
import DataGridStudentScores from './data-grid-student-scores'
import getProcessedQuestionData from '../util/get-processed-question-data'
import { useQuery } from 'react-query'
import { apiGetLO, apiGetResponsesForInstance, apiGetScoresForInstance } from '../util/api'
import RepositoryModal from './repository-modal'

export function ModalScoresByQuestionWithAPI({ onClose, instanceName, instID, loID }) {
	const { data: loData, isFetching: isLOFetching } = useQuery(['getLO', loID], apiGetLO, {
		initialStale: true,
		staleTime: Infinity,
		initialData: null
	})

	const { isError, data: responseData, isFetching } = useQuery(
		['apiGetResponsesForInstance', instID],
		apiGetResponsesForInstance,
		{
			initialStale: true,
			staleTime: Infinity,
			initialData: null
		}
	)

	// this will already be cached client side, lets use this data set to get the user names
	const { data: instanceScores, isFetching: instanceScoresIsFetching } = useQuery(
		['getScoresForInstance', instID],
		apiGetScoresForInstance,
		{
			initialStale: true,
			staleTime: Infinity,
			initialData: []
		}
	)

	const questionData = React.useMemo(() => {
		if (
			isLOFetching ||
			isFetching ||
			instanceScoresIsFetching ||
			isError ||
			!responseData ||
			!loData
		) {
			return []
		}

		// extract user name data from instanceScores
		const userMap = {}
		instanceScores.forEach(s => {
			userMap[s.userID] = s
		})

		// create an answer map where the key is the answer id
		const byAnswer = {}
		const byQuestion = {}
		responseData.forEach(ans => {
			const aID = ans.answer
			const qID = ans.itemID
			if (!byAnswer[aID]) {
				byAnswer[aID] = []
			}
			if (!byQuestion[qID]) {
				byQuestion[qID] = []
			}
			byAnswer[aID].push(ans)
			byQuestion[qID].push(ans)
		})

		// loop over questions
		const aGroup = loData.aGroup
		const questions = Object.values(getProcessedQuestionData(aGroup))
		questions.forEach(q => {
			q.id = q.originalQuestion.questionID
			q.score = null
			q.responses = []
			const scores = []

			switch (q.type) {
				case 'MC':
					// loop through answers of this question
					for (const i in q.originalQuestion.answers) {
						const a = q.originalQuestion.answers[i]
						const answerIndex = parseInt(i, 10)
						const answerLetter = String.fromCharCode(answerIndex + 65)
						if (byAnswer[a.answerID]) {
							const answerLogs = byAnswer[a.answerID] || []
							// total up scores
							// add answerIndex to each answer

							answerLogs.forEach(a => {
								a.answerIndex = answerIndex
								a.answerLetter = answerLetter
								a.userName = userMap[a.userID]?.userName || `Student ${a.userID}`
								a.response = answerLetter
								scores.push(parseFloat(a.score))
							})
							q.responses.push(...answerLogs)
						}
					}
					break

				case 'QA':
				case 'Media':
					{
						const answerLogs = byQuestion[q.id] || []
						answerLogs.forEach(a => {
							a.userName = userMap[a.userID]?.userName || `Student ${a.userID}`
							a.response = a.answer
							scores.push(parseFloat(a.score))
						})
						q.responses.push(...answerLogs)
					}
					break
			}

			if (scores.length) {
				q.score = scores.reduce((total, s) => total + s, 0) / scores.length
			}
		})
		return questions
	}, [responseData, loData, instanceScores])

	const ready = !isFetching && !isLOFetching && !instanceScoresIsFetching && loData && responseData

	if (!ready) return <div>Loading</div>
	if (isError) return <div>Error Loading Data</div>

	return <ModalScoresByQuestion instanceName={instanceName} data={questionData} onClose={onClose} />
}

export default function ModalScoresByQuestion({ data, instanceName, onClose }) {
	const [selectedItem, setSelectedItem] = React.useState()

	return (
		<RepositoryModal
			className="scoresByQuestion"
			instanceName={instanceName}
			onCloseModal={onClose}
		>
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
		</RepositoryModal>
	)
}

ModalScoresByQuestion.propTypes = {
	aGroup: PropTypes.shape({
		kids: PropTypes.arrayOf(
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
		)
	}),
	submitQuestionLogsByUser: PropTypes.object
}
