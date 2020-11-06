import './my-instances.scss'

import React, { useState } from 'react'
import PropTypes from 'prop-types'
import DataGridInstances from './data-grid-instances'
import RefreshButton from './refresh-button'
import SearchField from './search-field'

const getFilteredInstances = (instances, search) => {
	if (!instances) {
		return []
	}

	return instances.filter(instance => {
		if (!search) {
			return instances
		}

		search = search.toLowerCase()

		return (
			instance.name.toLowerCase().indexOf(search) > -1 ||
			instance.courseID.toLowerCase().indexOf(search) > -1 ||
			instance.instID.indexOf(search) > -1
		)
	})
}

export default function MyInstances({
	instances,
	selectedInstanceIndex,
	onSelect,
	onClickRefresh
}) {
	const [search, setSearch] = useState('')

	const selectedInstance =
		instances === null || selectedInstanceIndex === null ? null : instances[selectedInstanceIndex]
	const filteredInstances = getFilteredInstances(instances, search)
	const selectedIndex = filteredInstances.indexOf(selectedInstance)

	return (
		<div className="repository--my-instances">
			<h1>My Instances</h1>
			<div className="filter">
				<SearchField
					placeholder="Search by title, course or id"
					value={search}
					onChange={s => setSearch(s)}
				/>
				<RefreshButton onClick={onClickRefresh} />
			</div>
			<DataGridInstances
				data={filteredInstances}
				selectedIndex={selectedIndex === -1 ? null : selectedInstanceIndex}
				onSelect={onSelect}
			/>
		</div>
	)
}

MyInstances.propTypes = {
	instances: PropTypes.oneOfType([
		null,
		PropTypes.arrayOf(
			PropTypes.shape({
				instID: PropTypes.string.isRequired,
				name: PropTypes.string.isRequired,
				courseID: PropTypes.string.isRequired,
				startTime: PropTypes.string.isRequired,
				endTime: PropTypes.string.isRequired
			})
		)
	]),
	selectedInstanceIndex: PropTypes.oneOfType([null, PropTypes.number]),
	onSelect: PropTypes.func.isRequired,
	onClickRefresh: PropTypes.func.isRequired
}
