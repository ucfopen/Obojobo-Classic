import './repository-page.scss'

import React from 'react'
import { useMutation, useQuery, useQueryCache } from 'react-query'
import {
	// not using react-query yet
	apiLogout,
	apiGetLO,
	apiEditExtraAttempts,
	apiGetInstanceTrackingData,
	// api below are all using react-query
	apiGetInstances,
	apiGetScoresForInstance,
	apiGetUserNames,
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

const getFinalScoreFromAttemptScores = (scores, scoreMethod) => {
	switch (scoreMethod) {
		case 'h': // highest
			return Math.max.apply(null, scores)

		case 'r': // most recent
			return scores[scores.length - 1]

		case 'm': // average
			const sum = scores.reduce((acc, score) => acc + score, 0)
			return parseFloat(sum) / scores.length
	}

	return 0
}



const getScoresDataWithNewAttemptCount = (scoresForInstance, userID, newAttemptCount) => {
	return scoresForInstance.map(score => {
		if (score.userID !== userID) {
			return score
		}

		return {
			...score,
			attempts: { ...score.attempts, additional: newAttemptCount }
		}
	})
}

// hook used to load / cache users
const useApiGetUsersCached = (neededUserIDs) => {
	const [users, setUsers] = React.useState({})

	// filter out any users we already have in the cache
	const usersToLoad = React.useMemo(() => {
		return neededUserIDs.filter(id => !users[id])
	}, [neededUserIDs, users])

	//	load users
	const { isError, data, isFetching } = useQuery(
		['getUserNames', ...usersToLoad],
		apiGetUserNames, {
			initialStale: true,
			staleTime: Infinity,
			initialData: [],
			enabled: usersToLoad.length // load only after selectedInstance loads
		}
	)

	// process loaded users
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

	// load current user
	const { isError: qUserIsError, data: currentUser, error: qUserError } = useQuery('getUser', apiGetUser, {
		initialStale: true,
		staleTime: Infinity,
	})

	// load instances
	const { isError, data, error, isFetching } = useQuery(['getInstances'], apiGetInstances, {
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

	// extract list of user ids that can edit this instance
	const managerUserIDs = React.useMemo(() => {
		if (!qPermsData) return []
		// { <userID>: [<perm>, <perm>], ... } becomes [<userID>, <userID>]
		const userIds = Object.keys(qPermsData)
		// filter out any users that don't have '20' in perms
		return userIds.filter(id => qPermsData[id].includes('20'))
	}, [qPermsData])

	// get any users we don't already have
	const { users } = useApiGetUsersCached(managerUserIDs)

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
		return qScores.map(u => {
			const lastAttempt = u.attempts[u.attempts.length - 1]
			const scores = u.attempts.map(a => a.score)
			const finished = u.attempts.filter(a => Boolean(a.submitDate))
			const lastSubmitted = finished[finished.length - 1]?.submitDate
			const score = getFinalScoreFromAttemptScores(scores, selectedInstance.scoreMethod)

			return {
				user: `${u.user.last}, ${u.user.first}${u.user.mi ? ` ${u.user.mi}.` : ''}`,
				userID: u.userID,
				score,
				isScoreImported: lastAttempt.linkedAttempt !== 0,
				lastSubmitted,
				numAttemptsTaken: u.attempts.length,
				additional: u.additional,
				attemptCount: selectedInstance.attemptCount,
				isAttemptInProgress: !lastAttempt.submitted
			}
		})
	}, [qScores, qScoresIsFetching])


	const [mutateInstance] = useMutation(apiEditInstance)
	const [mutateExtraAttempts] = useMutation(apiEditExtraAttempts)


	// all the my instance props use
	const instanceSectionCallbacks = React.useMemo(() => ({
		onClickEditInstanceDetails: () => {
			const onSave = async (values) => {
				try{
					await mutateInstance(values, {throwOnError: true})

					// update 'data' in place
					// we have to do this because calling reloadInstances
					// doesnt update selectedInstance
					// which in turn doesnt update the instance details
					// till the user clicks on the current item in the instance datagrid
					const keys = Object.keys(values)

					const index = data.findIndex(d => d.instID == values.instID)
					const selected = data[index]
					keys.forEach(k => {selected[k] = values[k]})
					data[index] = {...selected}

					// trying to populate cache with updated data, but no dice
					// queryCache.setQueryData(['getInstances'], [...data])
					// only way I can get the dang instance list to update
					reloadInstances()

					setModal(null)
				} catch (error){
					console.error('Error changing Instance Details')
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

		onClickSetAdditionalAttempt: async (userID, attempts) => {
			try{
				await mutateExtraAttempts({userID, instID, newCount: attempts}, {throwOnError: true})
				queryCache.invalidateQueries(['getScoresForInstance', instID])
			} catch(e){
				console.error('Error setting extra attempts')
				console.error(e)
			}
		},

		onClickScoreDetails: (userName, userID) => {
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
