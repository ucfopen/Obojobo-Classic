import './modal-scores-by-question.scss'

import React from 'react'
import PropTypes from 'prop-types'
import QuestionScoreDetails from './question-score-details'
import DataGridStudentScores from './data-grid-student-scores'
import getProcessedQuestionData from '../util/get-processed-question-data'
import { useQuery } from 'react-query'
import { apiGetLO, apiGetInstanceTrackingData } from '../util/api'
import useApiGetUsersCached from '../hooks/use-api-get-users-cached'
import RepositoryModal from './repository-modal'

const getSubmitQuestionLogsForAssessment = logs => {
	let foundAssessmentSubmitQuestionLogs = false
	let responsesByQuestionID = {}
	let foundLogs = []

	logs.forEach(log => {
		if (log.itemType === 'SectionChanged' && log.valueA === '3') {
			foundAssessmentSubmitQuestionLogs = true
		} else if (
			(log.itemType === 'SectionChanged' && log.valueA !== '3') ||
			log.itemType === 'EndAttempt'
		) {
			foundLogs = foundLogs.concat(Object.values(responsesByQuestionID))
			foundAssessmentSubmitQuestionLogs = false
			responsesByQuestionID = {}
		}

		if (foundAssessmentSubmitQuestionLogs && log.itemType === 'SubmitQuestion') {
			responsesByQuestionID[log.valueA] = log
		}
	})

	return foundLogs
}

export function ModalScoresByQuestionWithAPI({ onClose, instanceName, instID, loID }) {
	const { isError, data, isFetching } = useQuery(
		['getInstanceTrackingData', instID],
		apiGetInstanceTrackingData,
		{
			initialStale: true,
			staleTime: Infinity
		}
	)

	// process tracking data
	const submitQuestionLogsByUserID = React.useMemo(() => {
		if (isFetching || isError || !data) return {}
		const result = {}
		data.visitLog.forEach(visit => {
			const { userID, logs } = visit
			// first encounter, create object
			if (!result[userID]) {
				result[userID] = {
					userName: `User #${userID}`,
					logs: []
				}
			}

			const submitLogs = getSubmitQuestionLogsForAssessment(logs)
			result[userID].logs = result[userID].logs.concat(submitLogs)
		})
		return result
	}, [data])

	// load user data
	const usersToLoad = React.useMemo(() => Object.keys(submitQuestionLogsByUserID), [
		submitQuestionLogsByUserID
	])
	const { users, isError: isUserError, isFetching: isUserFetching } = useApiGetUsersCached(
		usersToLoad
	)

	// populate userNames in the logs
	React.useMemo(() => {
		if (isUserFetching || isUserError) return
		usersToLoad.forEach(userID => {
			// when revisiting it may take a re-render for users to be populated
			if (users[userID]) submitQuestionLogsByUserID[userID].userName = users[userID].userString
		})
	}, [users, submitQuestionLogsByUserID])

	// load lo
	const { data: loData, isFetching: isLOFetching } = useQuery(['getLO', loID], apiGetLO, {
		initialStale: true,
		staleTime: Infinity,
		initialData: null,
		enabled: submitQuestionLogsByUserID
	})

	const ready = !isFetching && !isUserFetching && !isLOFetching && loData && data

	if (!ready) return <div>Loading</div>
	if (isError) return <div>Error Loading Data</div>

	return (
		<ModalScoresByQuestion
			instanceName={instanceName}
			submitQuestionLogsByUser={submitQuestionLogsByUserID}
			aGroup={loData?.aGroup || {}}
			onClose={onClose}
		/>
	)
}

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

export default function ModalScoresByQuestion({
	aGroup,
	submitQuestionLogsByUser,
	instanceName,
	onClose
}) {
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
