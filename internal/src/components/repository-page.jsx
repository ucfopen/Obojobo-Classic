import './repository-page.scss'

import React from 'react'
import { useQuery, useQueryCache } from 'react-query'
import {
	apiGetCurrentUser,
	apiVerifySession
} from '../util/api'
import MyInstances from './my-instances'
import LoadingIndicator from './loading-indicator'
import InstanceSection from './instance-section'
import Header from './header'

const RepositoryPage = () => {
	useQueryCache()
	const [sessionInterval, setSessionInterval] = React.useState(10000)
	const [displayError, setDisplayError] = React.useState(false)
	const [instance, setInstance] = React.useState(null)

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

	const theError = qSessionError || qUserError  || null
	if(!displayError && theError){
		setDisplayError(theError)
	}

	// disable the session checker
	// if interval isn't disabled and there is an error OR the user isn't logged in
	if(sessionInterval && (displayError || qSessionData === false)){
		setSessionInterval(false)
	}

	if (displayError) return <span>Error: {displayError?.message ?? displayError}</span>
	if (qSessionData === false) return <span>Not Logged in</span>
	if (!currentUser) return <LoadingIndicator isLoading={true} />
	return (
		<div id="repository" className="repository">
			<Header userName={currentUser.login} />
			<main>
				<div className="wrapper">
					<MyInstances onSelect={setInstance} />
					<InstanceSection
						instance={instance}
						currentUser={currentUser}
					/>
				</div>
			</main>
		</div>
	)
}

export default RepositoryPage
