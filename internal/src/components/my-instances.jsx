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
import { InstanceContext } from '../util/instance-context'

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

export default function MyInstances() {
	const { instance, setInstance } = React.useContext(InstanceContext)
	const [search, setSearch] = useState('')
	const [pickerVisible, hidePicker, showPicker] = useToggleState()
	const [pendingSelection, setPendingSelection] = React.useState(null)

	const newInstanceCreated = React.useCallback(instID => {
		setPendingSelection(parseInt(instID, 10))
		setInstance(null) // clears right pane of possibly currently selected item
		reloadInstances()
		hidePicker()
	}, [])

	const reloadInstances = React.useCallback(() => {
		queryCache.setQueryData(['getInstances'], null)
		queryCache.refetchQueries(['getInstances'], { exact: true })
	}, [])

	// new instance created listener
	React.useEffect(() => {
		const onNewInstance = event => {
			if (event.origin !== window.location.origin) return
			if (event?.data?.source === 'obojobo') {
				newInstanceCreated(event.data.instID)
			}
		}
		window.addEventListener('message', onNewInstance, false)
		return () => {
			window.removeEventListener('message', onNewInstance)
		} // cleanup function
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
		if (!data) return

		if (pendingSelection !== null) {
			const newInstance = data.find(i => i.instID === pendingSelection)
			// must test if it's found, data may not yet contain the pending selection
			if (newInstance) {
				setPendingSelection(null) // we've found the instance to select, so remove it
				setInstance(newInstance)
			}
		} else if (instance && data.indexOf(instance) === -1) {
			const reselectedInstance = data.find(i => i.instID === instance.instID)
			// must test if it's found, data may not yet contain the previous selection
			if (reselectedInstance) setInstance(reselectedInstance)
		}
	}, [data, instance])

	const instances = isFetching ? null : data
	const filteredInstances = React.useMemo(() => getFilteredInstances(instances, search), [
		instances,
		search
	])

	return (
		<div className="repository--my-instances">
			<div className="header">
				<h1>My Instances</h1>
				<Button onClick={showPicker} type="small" text="+ New Instance" />
			</div>
			<div className="filter">
				<SearchField
					placeholder="Search by title, course or id"
					value={search}
					onChange={setSearch}
				/>
				<RefreshButton onClick={reloadInstances} />
			</div>
			<DataGridInstances data={filteredInstances} onSelect={setInstance} instance={instance} />

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
	)
}
