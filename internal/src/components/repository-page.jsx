import './repository-page.scss'

import React, { useState, useCallback } from 'react'
import { useQuery, useQueryCache } from 'react-query'
import { apiGetInstances } from '../util/api'
import MyInstances from './my-instances'
import InstanceSection from './instance-section'
import Header from './header'

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

	if (isError) return <span>Error: {error.message}</span>

	return (
		<div className="repository">
			<Header />
			<main>
				<MyInstances
					instances={data}
					selectedInstanceIndex={selectedInstanceIndex}
					onSelect={row => setSelectedInstanceIndex(row)}
					onClickRefresh={() => reloadInstances()}
				/>
				<InstanceSection
					instance={selectedInstanceIndex !== null ? data[selectedInstanceIndex] : null}
				/>
			</main>
		</div>
	)
}

export default RepositoryPage
