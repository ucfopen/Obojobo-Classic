import React from 'react'
import PropTypes from 'prop-types'
import DataGridInstances from './data-grid-instances'
import RefreshButton from './refresh-button'
import SearchField from './search-field'

export default function MyInstances({
	instances,
	selectedInstanceIndex,
	onSelect,
	onClickRefresh,
}) {
	console.log('myi', selectedInstanceIndex)
	return (
		<div>
			<SearchField />
			<RefreshButton onClick={onClickRefresh} />
			<DataGridInstances
				data={instances}
				selectedIndex={selectedInstanceIndex}
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
