import './modal-score-details.scss'

import React from 'react'
import PropTypes from 'prop-types'
import DataGridStudentScores from './data-grid-student-scores'
import QuestionPreview from './question-preview'
import InstructionsFlag from './instructions-flag'
import AttemptDetails from './attempt-details'
import getProcessedQuestionData from '../util/get-processed-question-data'
import { useQuery } from 'react-query'
import { apiGetLO, apiGetVisitTrackingData } from '../util/api'
import RepositoryModal from './repository-modal'

const extractAssessmentAttemptData = (logs, aGroup) => {
	const foundLogs = []
	logs.forEach(log => {
		if (log.itemType === 'StartAttempt' && log.attemptData.attempt.qGroupID === aGroup.qGroupID) {
			// convenience method to make an ordered array of questionIds
			log.attemptData.attempt.questionOrder = log.attemptData.attempt.qOrder
				? log.attemptData.attempt.qOrder.split(',').map(id => parseInt(id, 10)) // alternates in use
				: aGroup.kids.map(q => q.questionID) // order is just as it is in the LO

			foundLogs.push(log.attemptData)
		}
	})
	return foundLogs
}

export function ModalScoreDetailsWithAPI({instanceName, onClose, userName, userID, instID, loID}){
	const { isError: isVisitDataError, data: visitData, isFetching: isVisitDataFetching } = useQuery(['visitTrackingData', userID, instID], apiGetVisitTrackingData, {
		cacheTime: 30000,
		initialStale: true,
		initialData: null,
		staleTime: Infinity
	})

	// note, can return cached value before visitData loads
	const { isError: isLOError, data: loData, isFetching: isLOFetching } = useQuery(['getLO', loID], apiGetLO, {
		initialStale: true,
		staleTime: Infinity,
		initialData: null,
		enabled: visitData
	})

	// merge some api states
	const isFetching = isVisitDataFetching || isLOFetching
	const isError = isVisitDataError || isLOError
	const ready = !isFetching && loData && visitData

	const props = React.useMemo(() => {
		if(isFetching || isError || !visitData || !loData) return {}
		const visitLogs = visitData.visitLog.map(vLog => vLog.logs).flat()
		const attemptLogs = extractAssessmentAttemptData(visitLogs, loData.aGroup)
		return { userName, attemptLogs, aGroup: loData.aGroup}
	}, [onClose, visitData, loData, isFetching])

	if(!ready) return <div>Loading</div>
	if(isError) return <div>Error Loading Data</div>
	return <ModalScoreDetails {...props} instanceName={instanceName} onClose={onClose} />
}

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
				attemptIndex: index + 1,
				answersSavedForAttempt: attemptLog.scores.length
			})
		})
	})

	// return the list, sorted by attemptIndex, questionNumber
	return answeredQuestions.sort((a, b) => {
		const attemptOrder = a.attemptIndex - b.attemptIndex
		if (attemptOrder !== 0) return attemptOrder
		return a.questionNumber - b.questionNumber
	})
}

export default function ModalScoreDetails({ aGroup, attemptLogs, userName, instanceName, onClose }) {
	const questionsByID = React.useMemo(() => getProcessedQuestionData(aGroup), [aGroup])
	const answeredQuestions = React.useMemo(() => getAnsweredQuestions(questionsByID, attemptLogs), [
		aGroup,
		attemptLogs,
		userName
	])
	const [selectedItem, setSelectedItem] = React.useState()

	const renderPreview = () => {
		if (!selectedItem) {
			return (
				<div className="instructions">
					<InstructionsFlag text="Select a question to see how the student answered" />
				</div>
			)
		}

		return (
			<div>
				<AttemptDetails
					attemptNumber={selectedItem.attemptIndex}
					score={selectedItem.attempt.score}
					numAnsweredQuestions={selectedItem.answersSavedForAttempt}
					numTotalQuestions={aGroup.quizSize}
					startTime={selectedItem.attempt.startTime}
					endTime={selectedItem.attempt.endTime}
				/>
				<hr />
				<QuestionPreview
					questionNumber={selectedItem.questionNumber}
					altNumber={selectedItem.altNumber}
					question={selectedItem.originalQuestion}
					score={selectedItem.score}
					response={selectedItem.answer}
				/>
			</div>
		)
	}

	return (
		<RepositoryModal
			className="scoreDetails"
			instanceName={instanceName}
			onCloseModal={onClose}

		>
			<div className="modal-score-details">
				<div className="left-pane">
					<div className="modal-title">
						<h3>Student Score Details</h3>
						<p>{userName}</p>
					</div>

					<div className="student-scores">
						<DataGridStudentScores
							showAttemptColumn={true}
							data={answeredQuestions}
							onSelect={row => {
								setSelectedItem(row)
							}}
						/>
					</div>
				</div>
				<div className="right-pane">{renderPreview()}</div>
			</div>
		</RepositoryModal>
	)
}

ModalScoreDetails.propTypes = {
	aGroup: PropTypes.object.isRequired,
	attemptLogs: PropTypes.array.isRequired,
	instanceName: PropTypes.string,
	onClose: PropTypes.func.isRequired,
	userName: PropTypes.string.isRequired,
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
					answerID: PropTypes.string.isRequired,
					answer: PropTypes.string.isRequired,
					score: PropTypes.number.isRequired
				})
			)
		})
	)
}
