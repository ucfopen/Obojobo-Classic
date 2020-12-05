import './instance-section.scss'

import React from 'react'
import PropTypes from 'prop-types'
import Button from './button'
import SectionHeader from './section-header'
import DefList from './def-list'
import AssessmentScoresSection from './assessment-scores-section'
import HelpButton from './help-button'
import InstructionsFlag from './instructions-flag'
import dayjs from 'dayjs'
import humanizeDuration from 'humanize-duration'
import { ModalAboutLOWithAPI } from './modal-about-lo'
import ModalInstanceDetails from './modal-instance-details'
import { apiEditInstance, apiGetInstancePerms } from '../util/api'
import { useQuery, useMutation, useQueryCache } from 'react-query'
import PeopleSearchDialog from './people-search-dialog'
import useToggleState from '../hooks/use-toggle-state'
import useApiGetUsersCached from '../hooks/use-api-get-users-cached'

const getScoringMethodText = scoreMethod => {
	switch (scoreMethod) {
		case 'h':
			return 'Highest Attempt'

		case 'm':
			return 'Attempt Average'

		case 'r':
			return 'Last Attempt'
	}
}

const getDurationText = (startTime, endTime) => {
	const now = dayjs()

	if (now.isAfter(endTime)) {
		return '(This instance is closed)'
	}

	if (now.isBefore(startTime)) {
		return 'This instance opens in ' + humanizeDuration(startTime - now, { largest: 2 })
	}

	return 'This instance closes in ' + humanizeDuration(endTime - now, { largest: 2 })
}

// hook to load instance managers for a particular instance
const useInstanceManagers = instID => {
	//	load perms to selected instance
	const {
		isError: qPermsIsError,
		data: qPermsData,
		error: qPermsError,
		isFetching: qPermsIsFetching
	} = useQuery(['getInstancePerms', instID], apiGetInstancePerms, {
		initialStale: true,
		staleTime: Infinity,
		initialData: null,
		enabled: instID // load only after selectedInstance loads
	})

	// extract list of user ids that can edit this instance
	const managerUserIDs = React.useMemo(() => {
		if (!qPermsData) return []
		// { <userID>: [<perm>, <perm>], ... } becomes [<userID>, <userID>]
		const userIds = Object.keys(qPermsData)
		// filter out any users that don't have '20' in perms
		return userIds.filter(id => qPermsData[id].includes('20'))
	}, [qPermsData])

	// get any users we don't already have
	const { users } = useApiGetUsersCached(managerUserIDs)

	const instanceManagers = React.useMemo(() => {
		const peeps = []
		managerUserIDs.forEach(id => {
			if (users[id]) peeps.push(users[id])
		})
		return peeps
	}, [managerUserIDs, users])

	return instanceManagers
}

export default function InstanceSection({
	instance,
	currentUser
}) {
	const queryCache = useQueryCache()
	const [aboutVisible, hideAbout, showAbout] = useToggleState()
	const [accessVisible, hideAccess, showAccess] = useToggleState()
	const [editVisible, hideEdit, showEdit] = useToggleState()

	const [mutateInstance] = useMutation(apiEditInstance)
	const onClickPreviewWithUrl = React.useCallback(() => {
		window.open(`/preview/${instance.loID}`, '_blank')
	}, [instance])

	const startTime = React.useMemo(() => dayjs(instance?.startTime * 1000), [instance?.startTime])
	const endTime = React.useMemo(() => dayjs(instance?.endTime * 1000), [instance?.endTime])

	const usersWithAccess = useInstanceManagers(instance?.instID)

	const updateInstance = React.useCallback(async values => {
		try {
			await mutateInstance(values, { throwOnError: true })
			// update 'data' in place
			const keys = Object.keys(values)
			keys.forEach(k => {instance[k] = values[k]})

			// trying to populate cache with updated data, but no dice
			const data = queryCache.getQueryData(['getInstances'])
			const index = data.indexOf(instance)
			data[index] = {...instance}
			queryCache.setQueryData(['getInstances'], [...data])
			queryCache.refetchQueries(['getInstances'], { exact: true })
			hideEdit()
		} catch (error) {
			console.error('Error changing Instance Details')
			console.error(error)
		}
	}, [instance, hideEdit])


	const detailItems = React.useMemo(() => {
		if(!instance) return []
		return [
			{
				label: 'Open Date',
				value: instance.externalLink ? '--' : startTime.format('MM/DD/YY - hh:mm A') + ' EST'
			},
			{
				label: 'Close Date',
				value: instance.externalLink ? '--' : endTime.format('MM/DD/YY - hh:mm A') + ' EST'
			},
			{
				value: instance.externalLink
					? '(This instance is being used in an external system)'
					: getDurationText(startTime, endTime)
			},
			{ label: 'Attempts Allowed', value: instance.attemptCount },
			{ label: 'Scoring Method', value: getScoringMethodText(instance.scoreMethod) },
			{
				label: 'Score Import',
				value: instance.allowScoreImport === '1' ? 'Enabled' : 'Disabled'
			}
		]
	}, [instance])

	if (!instance) {
		return (
			<div className="repository--instance-section is-empty">
				<InstructionsFlag text="Select an instance from the left" />
			</div>
		)
	}
	return (
		<div className="repository--instance-section is-not-empty">
			<div className="header">
				<div className="main-info">
					<h1>{instance.name}</h1>
					<h2>{`Course: ${instance.courseID}`}</h2>
				</div>
				<Button onClick={showAbout} type="text" text="About this learning object..." />
				<Button onClick={onClickPreviewWithUrl} type="large" text="Preview" />
			</div>

			<div className="link">
				<h3>Link</h3>
				{instance.externalLink ? (
					<div className="container">
						<input disabled type="text" value="--" />
						<HelpButton>
							<div>This instance is being used in an external system so no link is available</div>
						</HelpButton>
					</div>
				) : (
					<div className="container">
						<input readOnly type="text" value={`${window.origin}/view/${instance.instID}`} />
						<HelpButton>
							<div>
								Copy this link and distribute it to your students via online assignment, webcourses,
								course webpage or e-mail. Students will click on this ilnk to sign into Obojobo and
								take your instance.
							</div>
						</HelpButton>
					</div>
				)}
			</div>

			<SectionHeader label="Details" />
			<div className="details">
				<DefList items={detailItems} />
				<Button onClick={showEdit} type="small" text="Edit Details" />
			</div>

			<SectionHeader label="Ownership &amp; Access" />
			<div className="ownership">
				<ul>
					{usersWithAccess.map(userItem => {
						return (
							<li key={userItem.userID}>{`${userItem.userString}${
								userItem.userID === currentUser.userID ? ' (You)' : ''
							}`}</li>
						)
					})}
				</ul>
				<Button onClick={showAccess} type="small" text="Manage access" />
			</div>

			<AssessmentScoresSection instance={instance} />

			{aboutVisible
				? <ModalAboutLOWithAPI
						instanceName={instance.name}
						onClose={hideAbout}
						loID={instance.loID}
					/>
				: null
			}

			{accessVisible
				? <PeopleSearchDialog
					instanceName={instance.name}
					onClose={hideAccess}
					instID={instance.instID}
					currentUserId={currentUser.userID}
					usersWithAccess={usersWithAccess}
				/>
				:null
			}

			{editVisible
				?	<ModalInstanceDetails
						onClose={hideEdit}
						instanceName={instance.name}
						{...instance}
						onSave={updateInstance}
					/>
				: null
			}
		</div>
	)
}

InstanceSection.propTypes = {
	instance: PropTypes.object,
	scores: PropTypes.array,
	onClickAddAdditionalAttempt: PropTypes.func.isRequired,
	onClickRemoveAdditionalAttempt: PropTypes.func.isRequired
}
