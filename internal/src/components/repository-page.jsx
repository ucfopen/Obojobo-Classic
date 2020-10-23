import React, {useState, useCallback} from 'react'
import { useQuery, useQueryCache } from 'react-query'
import { apiGetInstances } from '../util/api'
import LoadingIndicator from './loading-indicator'
import DataGridInstances from './data-grid-instances'
import Responses from './responses'
import './repository-page.scss'
import MyInstances from './my-instances'
import InstanceSection from './instance-section'
import Header from './header'

const RepositoryPage = () => {
	const queryCache = useQueryCache()
	const reloadInstances = useCallback(() => {
		queryCache.invalidateQueries('getInstances')
	}, [])
	const { isLoading, isError, data, error }  = useQuery('getInstances', apiGetInstances, {initialStale: true, staleTime: 0})
	const [selectedInstance, setSelectedInstance] = useState(null)
	if (isError) return <span>Error: {error.message}</span>

	return (
		<React.Fragment>
		<Header />
		<main>
			<MyInstances instances={data} onSelect={row => setSelectedInstance(row) } />
			<InstanceSection instance={selectedInstance} />
		</main>
		</React.Fragment>
	)

	// return (
	// 	<div className="repository--wrapper">
	// 			<div></div>
	// 			<header>Header</header>
	// 			<div className="content-wrapper">
	// 				<div className="content-sidebar">
	// 					<button onClick={() => {reloadInstances()}}>Reload List</button>
	// 					<div className="instance-list">
	// 						<DataGridInstances data={data} isLoading={isLoading} onSelect={row => setSelectedInstanceID(row.instID) } />
	// 					</div>
	// 				</div>
	// 				<div className="content-main">
	// 					<Responses instID={selectedInstanceID} />
	// 				</div>
	// 			</div>
	// 			<footer>Footer</footer>
	// 			<LoadingIndicator isLoading={isLoading} />
	// 	</div>
	// )
}
export default RepositoryPage
