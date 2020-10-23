import React from 'react'
import { useQuery } from 'react-query'
import { apiGetResponsesForInstance } from '../util/api'
// import DataGridScores from './data-grid-scores'

const Responses = ({ instID }) => {
	const { isLoading, isError, data, error } = useQuery(
		['getResponsesForInstance', { instID }],
		apiGetResponsesForInstance,
		{ initialStale: true, staleTime: 0 }
	)
	if (isError) return <span>Error: {error.message}</span>
	return <div>@TODO</div>
	// return <DataGridScores data={data} isLoading={isLoading} />
}

export default Responses
