import React, {useState, useCallback} from 'react'
import { useQuery, useQueryCache } from 'react-query'
import { apiGetInstances } from '../util/api'
import LoadingIndicator from './loading-indicator'
import DataGridInstances from './data-grid-instances'
import Responses from './responses'
import './repository-page.scss'

const RepositoryPage = () => {
	const queryCache = useQueryCache()
	const reloadInstances = useCallback(() => {
		queryCache.invalidateQueries('getInstances')
	}, [])
	const { isLoading, isError, data, error }  = useQuery('getInstances', apiGetInstances, {initialStale: true, staleTime: 0})
	const [selectedInstanceID, setSelectedInstanceID] = useState(null)
	if (isError) return <span>Error: {error.message}</span>

	return (
		<div className="repository--wrapper">
				<div></div>
				<header>Header</header>
				<div className="content-wrapper">
					<div className="content-sidebar">
						<button onClick={() => {reloadInstances()}}>Reload List</button>
						<div className="instance-list">
							<DataGridInstances data={data} isLoading={isLoading} onSelect={row => setSelectedInstanceID(row.instID) } />
						</div>
					</div>
					<div className="content-main">
						<Responses instID={selectedInstanceID} />
					</div>
				</div>
				<footer>Footer</footer>
				<LoadingIndicator isLoading={isLoading} />
		</div>
	)
}
export default RepositoryPage
