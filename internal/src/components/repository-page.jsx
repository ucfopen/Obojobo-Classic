import './repository-page.scss'

import React from 'react'
import { useQuery, useQueryCache } from 'react-query'
import { apiGetCurrentUser, apiVerifySession } from '../util/api'
import MyInstances from './my-instances'
import LoadingIndicator from './loading-indicator'
import InstanceSection from './instance-section'
import Header from './header'
import getUserString from '../util/get-user-string'
import { InstanceContext } from '../util/instance-context'

const RepositoryPage = () => {
	useQueryCache()
	const [instance, setInstance] = React.useState(null)
	const [sessionInterval, setSessionInterval] = React.useState(15000)
	const [displayError, setDisplayError] = React.useState(false)

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
	const { data: currentUser, error: qUserError } = useQuery(
		'apiGetCurrentUser',
		apiGetCurrentUser,
		{
			initialStale: true,
			staleTime: Infinity,
			enabled: qSessionData
		}
	)

	const userName = React.useMemo(() => {
		if (!currentUser) return null
		return getUserString(currentUser)
	}, [currentUser])

	const theError = qSessionError || qUserError || null
	if (!displayError && theError) {
		setDisplayError(theError)
	}

	const state = React.useMemo(
		() => ({
			instance,
			setInstance
		}),
		[instance]
	)

	// disable the session checker
	// if interval isn't disabled and there is an error OR the user isn't logged in
	if (sessionInterval && (displayError || qSessionData === false)) {
		setSessionInterval(false)
	}

	if (displayError) return <span>Error: {displayError?.message ?? displayError}</span>
	if (qSessionData === false) return <span>Not Logged in</span>
	if (!currentUser) return <LoadingIndicator isLoading={true} />
	return (
		<InstanceContext.Provider value={state}>
			<div id="repository" className="repository">
				<Header userName={userName} />
				<main>
					<div className="wrapper">
						<MyInstances />
						<InstanceContext.Consumer>
							{({ instance }) => <InstanceSection instance={instance} currentUser={currentUser} />}
						</InstanceContext.Consumer>
					</div>
				</main>
			</div>
		</InstanceContext.Provider>
	)
}

export default RepositoryPage
