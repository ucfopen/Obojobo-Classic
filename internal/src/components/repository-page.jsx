import './repository-page.scss'

import React, { useState, useCallback, useEffect } from 'react'
import { useQuery, useQueryCache } from 'react-query'
import {
	apiGetInstances,
	apiGetLO,
	apiGetScoresForInstance,
	apiEditExtraAttempts
} from '../util/api'
import MyInstances from './my-instances'
import InstanceSection from './instance-section'
import Header from './header'
import RepositoryModals from './repository-modals'
import dayjs from 'dayjs'

const getFinalScoreFromAttemptScores = (attemptScores, scoringMethod) => {
	switch (scoringMethod) {
		case 'h':
			return Math.max.apply(null, attemptScores)

		case 'l':
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
	const reloadInstances = useCallback(() => {
		queryCache.invalidateQueries('getInstances')
		setSelectedInstanceIndex(null)
	}, null)
	const { isError, data, error } = useQuery('getInstances', apiGetInstances, {
		initialStale: true,
		staleTime: 0
	})
	const [selectedInstanceIndex, setSelectedInstanceIndex] = useState(null)
	const [modal, setModal] = useState(null)
	const [scoresForInstance, setScoresForInstance] = useState(null)

	if (isError) return <span>Error: {error.message}</span>

	const selectedInstance = selectedInstanceIndex === null ? null : data[selectedInstanceIndex]

	console.log('selectedInstance', selectedInstance)

	const onClickAboutThisLO = async () => {
		//@TODO: Is this how to do this?
		const loMeta = await apiGetLO(selectedInstance.loID)

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
			props: selectedInstance
		})
	}

	const onClickManageAccess = () => {
		alert('onClickManageAccess')
	}

	const onClickDownloadScores = () => {
		window.open(getCSVURLForInstance(selectedInstance))
	}

	const onClickViewScoresByQuestion = () => {
		setModal({
			type: 'scoresByQuestion',
			props: {}
		})
	}

	const onClickHeaderAboutOrBannerLink = () => {
		alert('onClickHeaderAboutOrBannerLink')
	}

	const onClickHeaderCloseBanner = () => {
		alert('onClickHeaderCloseBanner')
	}

	const onClickLogOut = () => {
		alert('onClickLogOut')
	}

	const onClickAddAdditionalAttempt = (userID, numAdditionalAttemptsAdded) => {
		apiEditExtraAttempts(userID, selectedInstance.instID, numAdditionalAttemptsAdded + 1)
		setScoresForInstance(
			getScoresDataWithNewAttemptCount(scoresForInstance, userID, numAdditionalAttemptsAdded + 1)
		)
	}

	const onClickRemoveAdditionalAttempt = (userID, numAdditionalAttemptsAdded) => {
		if (numAdditionalAttemptsAdded === 0) {
			alert('Unable to remove attempt!')
			return
		}

		apiEditExtraAttempts(userID, selectedInstance.instID, numAdditionalAttemptsAdded - 1)
		setScoresForInstance(
			getScoresDataWithNewAttemptCount(scoresForInstance, userID, numAdditionalAttemptsAdded - 1)
		)
	}

	const onClickScoreDetails = userID => {
		setModal({ type: 'scoreDetails', props: {} })
	}

	const onClickPreview = () => {
		if (selectedInstanceIndex === null) {
			return
		}

		window.open(`/preview/${selectedInstance.loID}`, '_blank')
	}

	const onSelectInstance = async row => {
		// setScoresForInstance(null)

		const scores = getAssessmentScoresFromAPIResult(
			await apiGetScoresForInstance(data[row].instID),
			data[row].scoreMethod,
			parseInt(data[row].attemptCount, 10)
		)

		setScoresForInstance(scores)

		setSelectedInstanceIndex(row)
	}

	const onClickRefreshScores = () => {
		//@TODO: Should be able to set this to null but for whatever reason this is causing a
		//max call stack size exceeded error
		setScoresForInstance([])

		const selectedInstID = selectedInstance.instID
		apiGetScoresForInstance(selectedInstID).then(scores => {
			if (selectedInstance.instID !== selectedInstID) {
				// The user has selected a different instance before we could return the results.
				// Ignore the return.
				return
			}

			setScoresForInstance(
				getAssessmentScoresFromAPIResult(
					scores,
					selectedInstance.scoreMethod,
					parseInt(selectedInstance.attemptCount, 10)
				)
			)
		})
	}

	return (
		<div id="repository" className="repository">
			<Header
				onClickAboutOrBannerLink={onClickHeaderAboutOrBannerLink}
				onClickCloseBanner={onClickHeaderCloseBanner}
				onClickLogOut={onClickLogOut}
			/>
			<main>
				<div className="wrapper">
					<MyInstances
						instances={data}
						selectedInstanceIndex={selectedInstanceIndex}
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
						instance={selectedInstanceIndex !== null ? data[selectedInstanceIndex] : null}
						scores={scoresForInstance}
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
