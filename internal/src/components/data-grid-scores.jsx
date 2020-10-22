import React from 'react'
import DataGrid from './data-grid'

const columns = [
	{accessor: 'userID', Header: 'User ID'},
	{accessor: 'itemID', Header: 'Question ID' },
	{accessor: 'score', Header: 'Score'},
]

const DataGridScores = ({data, isLoading}) => <DataGrid data={data} isLoading={isLoading} columns={columns} />

export default DataGridScores
