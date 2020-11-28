import './repository-page.scss'

import React from 'react'
import { useMutation, useQuery, useQueryCache } from 'react-query'
import {
	apiGetInstances, /* ! */
	apiGetLO,
	apiGetScoresForInstance,
	apiEditExtraAttempts,
	apiGetVisitTrackingData,
	apiLogout,
	apiGetInstanceTrackingData,
	apiGetUserNames,
	apiGetInstancePerms,
	apiGetUser, /* ! */
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
				isScoreImported: lastAttempt.linkedAttempt !== 0
			},
			lastSubmitted: lastAttempt.submitDate,
			attempts: {
				numAttemptsTaken: score.attempts.length,
				numAdditionalAttemptsAdded: parseInt(score.additional, 10),
				numAttempts: parseInt(attemptCount, 10),
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


const useCachedUsers = (neededUsers) => {
	const [users, setUsers] = React.useState({})

	// filter out any users we already have in the cache
	const usersToLoad = React.useMemo(() => {
		return neededUsers.filter(id => !users[id])
	}, [neededUsers, users])

	//	load user info for managers
	const { isError, data, isFetching } = useQuery(
		['getUserNames', ...usersToLoad],
		apiGetUserNames, {
			initialStale: true,
			staleTime: Infinity,
			initialData: [],
			enabled: usersToLoad.length // load only after selectedInstance loads
		}
	)

	React.useMemo(() => {
		// add a display string for each user
		const defaultUserName = {
			first: 'Unknown',
			last: 'User',
			mi: ''
		}

		const newUsers = {}
		data.forEach(user => {
			const u = {...user}
			u.userName = {...defaultUserName, ...u.userName}
			u.userString = `${u.userName.last}, ${u.userName.first}${u.userName.mi ? ' ' + u.userName.mi + '.' : ''}`
			newUsers[u.userID] = u
		})

		// add them to the cache for all loaded users
		setUsers({...users, ...newUsers})

	}, [data])

	return { users, isError, isFetching }
}

const RepositoryPage = () => {
	const [selectedInstance, setSelectedInstance] = React.useState(null)
	const instID = React.useMemo(() => selectedInstance ? selectedInstance.instID : null, [selectedInstance]) // caches testing if selectedInstance is null or not
	const [modal, setModal] = React.useState(null)
	const [isShowingBanner, setIsShowingBanner] = React.useState(
		typeof window.localStorage.hideBanner === 'undefined' ||
			window.localStorage.hideBanner === 'false'
	)
	const queryCache = useQueryCache()
	const reloadInstances = React.useCallback(() => {
		queryCache.invalidateQueries('getInstances')
	}, [])

	// Initial API calls

	// load current user
	const { isError: qUserIsError, data: currentUser, error: qUserError } = useQuery('getUser', apiGetUser, {
		initialStale: true,
		staleTime: Infinity,
	})


	// load instances
	const { isError, data, error, isFetching } = useQuery('getInstances', apiGetInstances, {
		initialStale: true,
		staleTime: Infinity,
		initialData: null,
		enabled: currentUser // load after user loads
	})

	//	load perms to selected instance
	const { isError: qPermsIsError, data: qPermsData, error: qPermsError, isFetching: qPermsIsFetching } = useQuery(
		['getInstancePerms', instID ],
		apiGetInstancePerms, {
			initialStale: true,
			staleTime: Infinity,
			initialData: null,
			enabled: instID // load only after selectedInstance loads
		}
	)

	const managerUserIDs = React.useMemo(() => {
		// extract list of user ids that can edit this instance
		// { <userID>: [<perm>, <perm>], ... } becomes [<userID>, <userID>]
		if (!qPermsData) return []
		const userIds = Object.keys(qPermsData)
		return userIds.filter(id => qPermsData[id].includes('20'))
	}, [qPermsData])

	const { users } = useCachedUsers(managerUserIDs)

	const instanceManagers = React.useMemo(() => {
		const peeps = []
		managerUserIDs.forEach(id => {
			if(users[id]) peeps.push(users[id])
		})
		return peeps
	}, [managerUserIDs, users])



	const { data: qScores, isFetching: qScoresIsFetching } = useQuery(
		['getScoresForInstance', instID],
		apiGetScoresForInstance, {
			initialStale: true,
			staleTime: Infinity,
			initialData: [],
			enabled: instID // load only after selectedInstance loads
		}
	)

	const scoresForInstance = React.useMemo(() => {
		if(!instID || qScoresIsFetching) return null
		return getAssessmentScoresFromAPIResult(qScores, selectedInstance.scoreMethod, selectedInstance.attemptCount)
	}, [qScores, qScoresIsFetching])


	const [mutateInstance] = useMutation(apiEditInstance)

	// all the my instance props use
	const instanceSectionCallbacks = React.useMemo(() => ({
		onClickEditInstanceDetails: () => {
			const onSave = async (values) => {
				try{
					await mutateInstance(values)

					// update 'data' in place
					// we have to do this because calling reloadInstances
					// doesnt update selectedInstance
					// which in turn doesnt update the instance details
					// till the user clicks on the current item in the instance datagrid
					const keys = Object.keys(values)

					const index = data.findIndex(d => d.instID == values.instID)
					const selected = data[index]
					keys.forEach(k => {selected[k] = values[k]})
					// data[index] = {...selected}

					// trying to populate cache with updated data, but no dice
					// queryCache.setQueryData('getInstances', [...data])

					// only way I can get the dang instance list to update
					reloadInstances()
					setModal(null)
				} catch (error){
					console.error(error)
				}
			}

			const {instID, name, courseID, startTime, endTime, attemptCount, externalLink, scoreMethod, allowScoreImport} = selectedInstance

			setModal({
				type: 'instanceDetails',
				props: {
					onSave,
					instID,
					name,
					courseID,
					startTime,
					endTime,
					attemptCount,
					scoreMethod,
					isExternallyLinked: externalLink,
					isImportAllowed: allowScoreImport
				}
			})
		},

		onClickAboutThisLO: () => {
			setModal({
				type: 'aboutThisLO',
				props: { loID: selectedInstance.loID }
			})
		},

		onClickPreview: (url) => {
			window.open(url, '_blank')
		},

		onClickManageAccess: () => {
			window.alert('onClickManageAccess')
		},

		onClickDownloadScores: (url) => {
			window.open(url)
		},

		onClickEditInstanceDetail: () => {
			setModal({
				type: 'instanceDetails',
				props: {
					instanceName: selectedInstance.name,
					courseName: selectedInstance.courseID,
					startTime: selectedInstance.startTime,
					endTime: selectedInstance.endTime,
					numAttempts: selectedInstance.attemptCount,
					scoringMethod: selectedInstance.scoreMethod,
					isImportAllowed: selectedInstance.allowScoreImport === '1',
					onSave: async values => {
						values.instID = selectedInstance.instID

						const oldSelectedInstanceIndex = selectedInstanceIndex
						await apiEditInstance(values)
						setModal(null)
						reloadInstances()
						// setSelectedInstanceIndex(oldSelectedInstanceIndex)
					}
				}
			})
		},

		onClickViewScoresByQuestion: async () => {
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
		},

		onClickRefreshScores: () => {
			queryCache.invalidateQueries(['getScoresForInstance', instID])
		},

		onClickAddAdditionalAttempt: (userID, numAdditionalAttemptsAdded) => {
			apiEditExtraAttempts(userID, selectedInstance.instID, numAdditionalAttemptsAdded + 1)
			setScoresForInstance(
				getScoresDataWithNewAttemptCount(scoresForInstance, userID, numAdditionalAttemptsAdded + 1)
			)
		},

		onClickRemoveAdditionalAttempt: (userID, numAdditionalAttemptsAdded) => {
			if (numAdditionalAttemptsAdded === 0) {
				window.alert('Unable to remove attempt!')
				return
			}

			apiEditExtraAttempts(userID, selectedInstance.instID, numAdditionalAttemptsAdded - 1)
			setScoresForInstance(
				getScoresDataWithNewAttemptCount(scoresForInstance, userID, numAdditionalAttemptsAdded - 1)
			)
		},

		onClickScoreDetails: (userName, userID) => {
			// const trackingData = await apiGetVisitTrackingData(userID, selectedInstance.instID)
			// const lo = await apiGetLO(selectedInstance.loID)

			// const visitLogs = trackingData.visitLog.map(visitLog => visitLog.logs).flat()
			// const attemptLogs = getStartAttemptLogsForAssessment(visitLogs).map(
			// 	startAttemptLog => startAttemptLog.attemptData
			// )

			setModal({
				type: 'scoreDetails',
				props: {
					userName,
					userID,
					instID: selectedInstance.instID,
					loID: selectedInstance.loID
				}
			})
		}

	}), [selectedInstance])

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


	if (isError) return <span>Error: {error.message}</span>
	if (!currentUser) return <LoadingIndicator isLoading={true} />

	return (
		<div id="repository" className="repository">
			<Header
				isShowingBanner={isShowingBanner}
				onClickAboutOrBannerLink={onClickHeaderAboutOrBannerLink}
				onClickCloseBanner={onClickHeaderCloseBanner}
				onClickLogOut={onClickLogOut}
				userName={currentUser.login}
			/>
			<main>
				<div className="wrapper">
					<MyInstances
						instances={isFetching ? null : data}
						onSelect={setSelectedInstance}
						onClickRefresh={reloadInstances}
					/>
					<InstanceSection
						{...instanceSectionCallbacks}
						instance={selectedInstance}
						scores={scoresForInstance}
						usersWithAccess={instanceManagers}
						user={currentUser}
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
