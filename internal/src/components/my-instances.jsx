import './my-instances.scss'

import React, { useState } from 'react'
import PropTypes from 'prop-types'
import DataGridInstances from './data-grid-instances'
import RefreshButton from './refresh-button'
import SearchField from './search-field'

const getFilteredInstances = (instances, search) => {
	// is still loading?
	if (!instances) return null
	if (!search) return instances
	search = search.toLowerCase()

	// data loaded, filter
	return instances.filter(instance => {
		return (
			instance.name.toLowerCase().indexOf(search) > -1 ||
			instance.courseID.toLowerCase().indexOf(search) > -1 ||
			String(instance.instID).indexOf(search) > -1
		)
	})
}

export default function MyInstances({ instances, onSelect, onClickRefresh }) {
	const [search, setSearch] = useState('')

	const filteredInstances = React.useMemo(() => getFilteredInstances(instances, search), [instances, search])

	return (
		<div className="repository--my-instances">
			<h1>My Instances</h1>
			<div className="filter">
				<SearchField
					placeholder="Search by title, course or id"
					value={search}
					onChange={setSearch}
				/>
				<RefreshButton onClick={onClickRefresh} />
			</div>
			<DataGridInstances data={filteredInstances} onSelect={onSelect} />
		</div>
	)
}

MyInstances.propTypes = {
	instances: PropTypes.arrayOf(
		PropTypes.shape({
			instID: PropTypes.number.isRequired,
			name: PropTypes.string.isRequired,
			courseID: PropTypes.string.isRequired,
			startTime: PropTypes.number.isRequired,
			endTime: PropTypes.number.isRequired
		})
	),
	selectedInstanceIndex: PropTypes.number,
	onSelect: PropTypes.func.isRequired,
	onClickRefresh: PropTypes.func.isRequired
}
