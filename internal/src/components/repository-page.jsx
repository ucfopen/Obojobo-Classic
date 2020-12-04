import './repository-page.scss'

import React from 'react'
import { useQuery, useQueryCache } from 'react-query'
import {
	// not using react-query yet
	apiLogout,
	// api below are all using react-query
	apiGetInstances,
	apiGetInstancePerms,
	apiGetCurrentUser,
	apiVerifySession
} from '../util/api'
import useApiGetUsersCached from '../hooks/use-api-get-users-cached'
import MyInstances from './my-instances'
import LoadingIndicator from './loading-indicator'
import InstanceSection from './instance-section'
import Header from './header'
import RepositoryModals from './repository-modals'
import ModalAboutObojoboNext from './modal-about-obojobo-next'

const RepositoryPage = () => {
	const [sessionInterval, setSessionInterval] = React.useState(10000)
	const [displayError, setDisplayError] = React.useState(false)
	const [selectedInstance, setSelectedInstance] = React.useState(null)
	const instID = React.useMemo(() => (selectedInstance ? selectedInstance.instID : null), [
		selectedInstance
	]) // caches testing if selectedInstance is null or not
	const [modal, setModal] = React.useState(null)
	const closeModal = React.useCallback(() => setModal(null), [])
	const queryCache = useQueryCache()
	const reloadInstances = React.useCallback(() => {
		queryCache.invalidateQueries('getInstances')
	}, [])

	const { data: qSessionData, error: qSessionError } = useQuery(
		'apiVerifySession',
		apiVerifySession,
		{
			initialStale: true,
			staleTime: 5000,
			refetchInterval: sessionInterval,
			refetchIntervalInBackground: sessionInterval * 3,
			notifyOnStatusChange: true,
			enabled: displayError === false || qSessionData === false
		}
	)

	// load current user
	const { isError: qUserIsError, data: currentUser, error: qUserError } = useQuery(
		'apiGetCurrentUser',
		apiGetCurrentUser,
		{
			initialStale: true,
			staleTime: Infinity,
			enabled: qSessionData
		}
	)

	// load instances
	const { data, error, isFetching } = useQuery(['getInstances'], apiGetInstances, {
		cacheTime: Infinity,
		initialStale: true,
		staleTime: Infinity,
		initialData: null,
		enabled: currentUser // load after user loads
	})

	// this is needed to detect when data is reloaded and selectedInstance is
	// referencing an object from the old results.  This forces the update
	// to the new selected instance data
	React.useEffect(() => {
		if (!data || !selectedInstance) return
		if (data.indexOf(selectedInstance) == -1) {
			console.log('updated')
			const newInstance = data.find(i => i.instID === selectedInstance.instID)
			setSelectedInstance(newInstance)
		}
	}, [data, selectedInstance])

	//	load perms to selected instance
	const {
		isError: qPermsIsError,
		data: qPermsData,
		error: qPermsError,
		isFetching: qPermsIsFetching
	} = useQuery(['getInstancePerms', instID], apiGetInstancePerms, {
		initialStale: true,
		staleTime: Infinity,
		initialData: null,
		enabled: instID // load only after selectedInstance loads
	})

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
			if (users[id]) peeps.push(users[id])
		})
		return peeps
	}, [managerUserIDs, users])

	const onClickHeaderBanner = React.useCallback(() => {
		setModal({
			component: ModalAboutObojoboNext,
			className: 'aboutObojoboNext',
			props: {}
		})
	}, [])

	const onClickLogOut = React.useCallback(async () => {
		await apiLogout()
		window.location.reload(false)
	}, [])

	const theError = qSessionError || qUserError || error || qPermsError || null
	if (!displayError && theError) {
		setDisplayError(theError)
	}
	if (sessionInterval && (displayError || qSessionData === false)) {
		setSessionInterval(false)
	}
	if (displayError) return <span>Error: {displayError?.message ?? displayError}</span>
	if (qSessionData === false) {
		onClickLogOut()
		return <span>Not Logged in</span>
	}
	if (!currentUser) return <LoadingIndicator isLoading={true} />

	return (
		<div id="repository" className="repository">
			<Header
				onClickBanner={onClickHeaderBanner}
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
						instance={selectedInstance}
						usersWithAccess={instanceManagers}
						user={currentUser}
						setModal={setModal}
					/>
				</div>
			</main>
			{modal ? (
				<RepositoryModals
					modal={modal}
					className={modal.className}
					instanceName={selectedInstance?.name || ''}
					onCloseModal={closeModal}
				/>
			) : null}
		</div>
	)
}

export default RepositoryPage
