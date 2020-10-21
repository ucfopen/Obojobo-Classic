import React, {useState, useCallback} from 'react'
import { useQuery, usePaginatedQuery, useMutation, useQueryCache, QueryCache, ReactQueryCacheProvider, useIsFetching } from 'react-query'

import { apiGetInstances, apiGetResponsesForInstance} from '../util/api'
import LoadingIndicator from './loading-indicator'
import DataGrid from './data-grid'
import './repository-page.scss'

const Scores = ({instID}) => {
	const { isLoading, isError, data, error }  = useQuery(['getResponsesForInstance', { instID }], apiGetResponsesForInstance, {staleTime: 60*1000})

	if (isLoading) return <span>Loading...</span>
	if (isError) return <span>Error: {error.message}</span>

	return (
		<>
			{data.map((score, index) => {
				return <div key={index}>userID: {score.userID} itemID: {score.itemID} score: {score.score}</div>
			})}
		</>
	)
}


const RepositoryPage = () => {
	const queryCache = useQueryCache()
	const reloadInstances = useCallback(() => {
		queryCache.invalidateQueries('getInstances')
	}, [])
	const { isLoading, isError, data, error }  = useQuery('getInstances', apiGetInstances, {initialData: [], initialStale: true})
	const [selectedInstanceID, setSelectedInstanceID] = useState(null)


	if (isLoading) return <span>Loading...</span>
	if (isError) return <span>Error: {error.message}</span>

	return (
		<div className="repository--wrapper">
				<div></div>
				<header>Header</header>
				<div className="content-wrapper">
					<div className="content-sidebar">
						<button onClick={() => {reloadInstances()}}>Reload List</button>
						<div className="instance-list">
							<DataGrid data={data} />
						</div>
					</div>
					<div className="content-main">
						<Scores instID={selectedInstanceID} />
					</div>
				</div>
				<footer>Footer</footer>
				<LoadingIndicator isLoading={useIsFetching()} />
		</div>
	)
}
export default RepositoryPage
