import React from 'react'
import PropTypes from 'prop-types'
import DataGridInstances from './data-grid-instances'
import RefreshButton from './refresh-button'
import SearchField from './search-field'

export default function MyInstances({ instances, onSelect, onClickRefresh }) {
	return (
		<div>
			<SearchField />
			<RefreshButton onClick={onClickRefresh} />
			<DataGridInstances data={instances} onSelect={onSelect} />
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
				endTime: PropTypes.string.isRequired,
			})
		),
	]),
	onSelect: PropTypes.func.isRequired,
	onClickRefresh: PropTypes.func.isRequired,
}
