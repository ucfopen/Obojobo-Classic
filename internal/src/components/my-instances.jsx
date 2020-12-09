import './my-instances.scss'

import React, { useState } from 'react'
import PropTypes from 'prop-types'
import DataGridInstances from './data-grid-instances'
import RefreshButton from './refresh-button'
import SearchField from './search-field'
import { useQuery, queryCache } from 'react-query'
import { apiGetInstances } from '../util/api'
import Button from './button'
import useToggleState from '../hooks/use-toggle-state'
import RepositoryModal from './repository-modal'

const getFilteredInstances = (instances, search) => {
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

export default function MyInstances({ onSelect }) {
	const [search, setSearch] = useState('')
	const [_selectedInstance, _setSelectedInstance] = React.useState(null)
	const [pickerVisible, hidePicker, showPicker] = useToggleState()
	const setSelectedInstance = React.useCallback(
		instance => {
			_setSelectedInstance(instance)
			onSelect(instance)
		},
		[onSelect]
	)

	const reloadInstances = React.useCallback(() => {
		queryCache.invalidateQueries('getInstances')
	}, [])

	// new instance listener
	React.useEffect(() => {
		const onNewInstance = (event) => {
			if (event.origin !== window.location.origin) return
			if (event?.data?.source === 'obojobo'){
				hidePicker()
				reloadInstances()
			}
		}
		window.addEventListener('message', onNewInstance, false)
		return () => {window.removeEventListener('message', onNewInstance)} // cleanup function
	}, [])

	// load instances
	const { data, isFetching } = useQuery(['getInstances'], apiGetInstances, {
		cacheTime: Infinity,
		initialStale: true,
		staleTime: Infinity,
		initialData: null
	})

	// this is needed to detect when data is reloaded and _selectedInstance is
	// referencing an object from the old results.  This forces the update
	// to the new selected instance data
	React.useEffect(() => {
		if (!data || !_selectedInstance) {
			// _setSelectedInstance(null)
			onSelect(null)
			return
		}

		if (data.indexOf(_selectedInstance) === -1) {
			const newInstance = data.find(i => i.instID === _selectedInstance.instID)
			_setSelectedInstance(newInstance)
		}
	}, [data, _selectedInstance])

	const instances = isFetching ? null : data
	const filteredInstances = React.useMemo(() => getFilteredInstances(instances, search), [
		instances,
		search
	])

	return (
		<div className="repository--my-instances">
			<h1>My Instances</h1>
			<Button onClick={showPicker} type="small" text="New Instance" />
			<div className="filter">
				<SearchField
					placeholder="Search by title, course or id"
					value={search}
					onChange={setSearch}
				/>
				<RefreshButton onClick={reloadInstances} />
			</div>
			<DataGridInstances data={filteredInstances} onSelect={setSelectedInstance} />
			{pickerVisible ? (
				<RepositoryModal
					className="instanceDetails"
					instanceName="Create an Instance"
					onCloseModal={hidePicker}
				>
					<div className="modal-new-instance">
						<iframe src="/lti/picker.php?repository=1"></iframe>
					</div>
				</RepositoryModal>
			) : null}
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
	_selectedInstanceIndex: PropTypes.number,
	onSelect: PropTypes.func.isRequired
}
