import './repository-page.scss'

import React from 'react'
import { useQuery, useQueryCache } from 'react-query'
import {
	apiGetInstances,
	apiGetLO,
	apiGetLOMeta,
	apiGetScoresForInstance,
	apiEditExtraAttempts,
	apiGetVisitTrackingData,
	apiLogout,
	apiGetInstanceTrackingData,
	apiGetInstancePerms,
	apiGetUser,
	apiEditInstance
} from '../util/api'
import getUsers from '../util/get-users'
import MyInstances from './my-instances'
import LoadingIndicator from './loading-indicator'
import InstanceSection from './instance-section'
import Header from './header'
import RepositoryModals from './repository-modals'
import dayjs from 'dayjs'

const getStartAttemptLogsForAssessment = logs => {
	let foundAssessmentSubmitQuestionLogs = false
	const foundLogs = []

	logs.forEach(log => {
		if (log.itemType === 'SectionChanged' && log.valueA === '3') {
			foundAssessmentSubmitQuestionLogs = true
		} else if (
			(log.itemType === 'SectionChanged' && log.valueA !== '3') ||
			log.itemType === 'EndAttempt'
		) {
			foundAssessmentSubmitQuestionLogs = false
		}

		if (foundAssessmentSubmitQuestionLogs && log.itemType === 'StartAttempt') {
			foundLogs.push(log)
		}
	})

	return foundLogs
}

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

const getFinalScoreFromAttemptScores = (attemptScores, scoringMethod) => {
	switch (scoringMethod) {
		case 'h':
			return Math.max.apply(null, attemptScores)

		case 'r':
			return attemptScores[attemptScores.length - 1]

		case 'm':
			return attemptScores.reduce((acc, score) => acc + score, 0) / attemptScores.length
	}

	return 0
}

const getAssessmentScoresFromAPIResult = (scoresByUser, scoringMethod, attemptCount) => {
	return scoresByUser.map(score => {
		const lastAttempt = score.attempts[score.attempts.length - 1]

		return {
			user: `${score.user.last}, ${score.user.first}${score.user.mi ? ` ${score.user.mi}.` : ''}`,
			userID: score.userID,
			score: {
				value: getFinalScoreFromAttemptScores(
					score.attempts.map(attempt => parseFloat(attempt.score)),
					scoringMethod
				),
				isScoreImported: lastAttempt.linkedAttempt !== '0'
			},
			lastSubmitted: lastAttempt.submitDate,
			attempts: {
				numAttemptsTaken: score.attempts.length,
				numAdditionalAttemptsAdded: parseInt(score.additional, 10),
				numAttempts: attemptCount,
				isAttemptInProgress: !lastAttempt.submitted
			}
		}
	})
}

const getScoresDataWithNewAttemptCount = (scoresForInstance, userID, newAttemptCount) => {
	return scoresForInstance.map(score => {
		if (score.userID !== userID) {
			return score
		}

		return {
			...score,
			attempts: { ...score.attempts, numAdditionalAttemptsAdded: newAttemptCount }
		}
	})
}

const getCSVURLForInstance = ({ instID, name, courseID, scoreMethod }) => {
	const instName = encodeURI(name.replace(/ /g, '_'))
	const courseName = encodeURI(courseID.replace(/ /g, '_'))
	const date = dayjs().format('MM-DD-YY')

	return `/assets/csv.php?function=scores&instID=${instID}&filename=${instName}_-_${courseName}_-_${date}&method=${scoreMethod}`
}

const RepositoryPage = () => {

	const queryCache = useQueryCache()
	const reloadInstances = React.useCallback(() => {
		queryCache.invalidateQueries('getInstances')
	}, [])

	// load user
	const { isError: qUserIsError, data: user, error: qUserError } = useQuery('getUser', apiGetUser, {
		initialStale: true,
		staleTime: Infinity,
	})

	// load instances
	const { isError, data, error, isFetching } = useQuery('getInstances', apiGetInstances, {
		initialStale: true,
		staleTime: Infinity,
		initialData: null
	})

	const [selectedInstance, setSelectedInstance] = React.useState(null)
	const [modal, setModal] = React.useState(null)
	const [usersWithAccess, setUsersWithAccessForInstance] = React.useState(null)
	const [scoresForInstance, setScoresForInstance] = React.useState(null)
	const [isShowingBanner, setIsShowingBanner] = React.useState(
		typeof window.localStorage.hideBanner === 'undefined' ||
			window.localStorage.hideBanner === 'false'
	)

	if (isError) return <span>Error: {error.message}</span>

	const onClickAboutThisLO = async () => {
		//@TODO: Is this how to do this?
		const loMeta = await apiGetLOMeta(selectedInstance.loID)

		setModal({
			type: 'aboutThisLO',
			props: {
				learnTime: loMeta.learnTime,
				language: loMeta.language,
				numContentPages: loMeta.summary.contentSize,
				numPracticeQuestions: loMeta.summary.practiceSize,
				numAssessmentQuestions: loMeta.summary.assessmentSize,
				authorNotes: loMeta.notes,
				learningObjective: loMeta.objective
			}
		})
	}

	const onClickEditInstanceDetails = () => {
		setModal({
			type: 'instanceDetails',
			props: {
				instanceName: selectedInstance.name,
				courseName: selectedInstance.courseID,
				startTime: selectedInstance.startTime,
				endTime: selectedInstance.endTime,
				numAttempts: parseInt(selectedInstance.attemptCount, 10),
				scoringMethod: selectedInstance.scoreMethod,
				isImportAllowed: selectedInstance.allowScoreImport === '1',
				onSave: async values => {
					values.instID = selectedInstance.instID

					const oldSelectedInstanceIndex = selectedInstanceIndex

					await apiEditInstance(values)
					setModal(null)
					reloadInstances()
					setSelectedInstanceIndex(oldSelectedInstanceIndex)
				}
			}
		})
	}

	const onClickManageAccess = () => {
		window.alert('onClickManageAccess')
	}

	const onClickDownloadScores = () => {
		window.open(getCSVURLForInstance(selectedInstance))
	}

	const onClickViewScoresByQuestion = async () => {
		const trackingData = await apiGetInstanceTrackingData(selectedInstance.instID)
		const lo = await apiGetLO(selectedInstance.loID)

		const submitQuestionLogsByUserID = {}
		const userIDsToFetch = []

		trackingData.visitLog.forEach(visitLog => {
			if (!submitQuestionLogsByUserID[visitLog.userID]) {
				userIDsToFetch.push(visitLog.userID)

				submitQuestionLogsByUserID[visitLog.userID] = {
					userName: `User #${visitLog.userID}`,
					logs: []
				}
			}

			submitQuestionLogsByUserID[visitLog.userID].logs = submitQuestionLogsByUserID[
				visitLog.userID
			].logs.concat(getSubmitQuestionLogsForAssessment(visitLog.logs))
		})

		const users = await getUsers(userIDsToFetch)
		users.forEach(userItem => {
			submitQuestionLogsByUserID[userItem.userID].userName = userItem.userString
		})

		setModal({
			type: 'scoresByQuestion',
			props: {
				submitQuestionLogsByUser: Object.values(submitQuestionLogsByUserID),
				questions: lo.aGroup.kids
			}
		})
	}

	const onClickHeaderAboutOrBannerLink = () => {
		setModal({
			type: 'aboutObojoboNext',
			props: {}
		})
	}

	const onClickHeaderCloseBanner = () => {
		window.localStorage.hideBanner = 'true'
		setIsShowingBanner(false)
	}

	const onClickLogOut = async () => {
		await apiLogout()
		window.location = window.location
	}

	const onClickAddAdditionalAttempt = (userID, numAdditionalAttemptsAdded) => {
		apiEditExtraAttempts(userID, selectedInstance.instID, numAdditionalAttemptsAdded + 1)
		setScoresForInstance(
			getScoresDataWithNewAttemptCount(scoresForInstance, userID, numAdditionalAttemptsAdded + 1)
		)
	}

	const onClickRemoveAdditionalAttempt = (userID, numAdditionalAttemptsAdded) => {
		if (numAdditionalAttemptsAdded === 0) {
			window.alert('Unable to remove attempt!')
			return
		}

		apiEditExtraAttempts(userID, selectedInstance.instID, numAdditionalAttemptsAdded - 1)
		setScoresForInstance(
			getScoresDataWithNewAttemptCount(scoresForInstance, userID, numAdditionalAttemptsAdded - 1)
		)
	}

	const onClickScoreDetails = async (userName, userID) => {
		const trackingData = await apiGetVisitTrackingData(userID, selectedInstance.instID)
		const lo = await apiGetLO(selectedInstance.loID)

		const visitLogs = trackingData.visitLog.map(visitLog => visitLog.logs).flat()
		const attemptLogs = getStartAttemptLogsForAssessment(visitLogs).map(
			startAttemptLog => startAttemptLog.attemptData
		)

		setModal({ type: 'scoreDetails', props: { userName, attemptLogs, questions: lo.aGroup.kids } })
	}

	const onClickPreview = () => {
		if (selectedInstance === null) {
			return
		}

		window.open(`/preview/${selectedInstance.loID}`, '_blank')
	}

	const onSelectInstance = async (selectedInstance) => {
		const scores = getAssessmentScoresFromAPIResult(
			await apiGetScoresForInstance(selectedInstance.instID),
			selectedInstance.scoreMethod,
			parseInt(selectedInstance.attemptCount, 10)
		)

		const perms = await apiGetInstancePerms(selectedInstance.instID)
		const managerUserIDs = []
		Object.keys(perms).forEach(userID => {
			if (perms[userID].indexOf('20') > -1) {
				managerUserIDs.push(userID)
			}
		})

		const users = await getUsers(managerUserIDs)

		setUsersWithAccessForInstance(users)
		setScoresForInstance(scores)
		setSelectedInstance(selectedInstance)
	}

	const onClickRefreshScores = React.useMemo(() => async () => {
		setScoresForInstance(null)

		const selectedInstID = selectedInstance.instID
		const scores = await apiGetScoresForInstance(selectedInstID)
		if (selectedInstance.instID !== selectedInstID) {
			// The user has selected a different instance before we could return the results.
			// Ignore the return.
			return
		}

		const formattedScores = getAssessmentScoresFromAPIResult(
			scores,
			selectedInstance.scoreMethod,
			parseInt(selectedInstance.attemptCount, 10)
		)

		setScoresForInstance(formattedScores)

	}, [selectedInstance])

	if (!user) {
		return <LoadingIndicator isLoading={true} />
	}

	return (
		<div id="repository" className="repository">
			<Header
				isShowingBanner={isShowingBanner}
				onClickAboutOrBannerLink={onClickHeaderAboutOrBannerLink}
				onClickCloseBanner={onClickHeaderCloseBanner}
				onClickLogOut={onClickLogOut}
				userName={user.login}
			/>
			<main>
				<div className="wrapper">
					<MyInstances
						instances={isFetching ? null : data}
						onSelect={onSelectInstance}
						onClickRefresh={() => reloadInstances()}
					/>
					<InstanceSection
						onClickAboutThisLO={onClickAboutThisLO}
						onClickPreview={onClickPreview}
						onClickEditInstanceDetails={onClickEditInstanceDetails}
						onClickManageAccess={onClickManageAccess}
						onClickDownloadScores={onClickDownloadScores}
						onClickViewScoresByQuestion={onClickViewScoresByQuestion}
						onClickRefreshScores={onClickRefreshScores}
						onClickAddAdditionalAttempt={onClickAddAdditionalAttempt}
						onClickRemoveAdditionalAttempt={onClickRemoveAdditionalAttempt}
						onClickScoreDetails={onClickScoreDetails}
						instance={selectedInstance}
						scores={scoresForInstance}
						usersWithAccess={usersWithAccess}
						user={user}
					/>
				</div>
			</main>
			{modal ? (
				<RepositoryModals
					instanceName={selectedInstance ? selectedInstance.name : ''}
					modalType={modal.type}
					modalProps={modal.props}
					onCloseModal={() => setModal(null)}
				/>
			) : null}
		</div>
	)
}

export default RepositoryPage
