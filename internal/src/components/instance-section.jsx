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
import { apiEditInstance } from '../util/api'
import { useMutation, useQueryCache } from 'react-query'
import PeopleSearchDialog from './people-search-dialog'
import { ModalScoresByQuestionWithAPI } from './modal-scores-by-question'

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

export default function InstanceSection({
	instance,
	usersWithAccess,
	user,
	setModal
}) {
	const queryCache = useQueryCache()
	const [mutateInstance] = useMutation(apiEditInstance)


	const onClickPreviewWithUrl = React.useCallback(() => {
		window.open(`/preview/${instance.loID}`, '_blank')
	}, [instance])

	const startTime = React.useMemo(() => dayjs(instance?.startTime * 1000), [instance?.startTime])
	const endTime = React.useMemo(() => dayjs(instance?.endTime * 1000), [instance?.endTime])

	const onClickAboutThisLO = React.useCallback(() => {
		setModal({
			component: ModalAboutLOWithAPI,
			className: 'aboutThisLO',
			props: {
				loID: instance.loID
			}
		})
	}, [instance])



	const onClickEditInstanceDetails = React.useCallback(() => {

		const onSave = async values => {
			try {
				await mutateInstance(values, { throwOnError: true })
				// update 'data' in place
				const keys = Object.keys(values)
				keys.forEach(k => {
					instance[k] = values[k]
				})

				// trying to populate cache with updated data, but no dice
				const data = queryCache.getQueryData(['getInstances'])
				const index = data.indexOf(instance)
				data[index] = {...instance}
				queryCache.setQueryData(['getInstances'], [...data])
				queryCache.refetchQueries(['getInstances'], { exact: true })
				setModal(null)
			} catch (error) {
				console.error('Error changing Instance Details')
				console.error(error)
			}
		}

		setModal({
			component: ModalInstanceDetails,
			className: 'instanceDetails',
			props: {...instance, onSave }
		})
	}, [instance, mutateInstance, setModal])

	const onClickManageAccess = React.useCallback(() => {
		setModal({
			component: PeopleSearchDialog,
			className: 'peopleSearch',
			props: {
				currentUserId: user.userID,
				clearPeopleSearchResults: () => {},
				onSelectPerson: () => {},
				onClose: () => {},
				onSearchChange: () => {},
				people: [{id: 5, avatarUrl: '/assets/images/user-circle.svg', firstName: 'Demo', lastName: 'man', username: 'demoman'}]
			}
		})
	}, [user, usersWithAccess])



	const onClickViewScoresByQuestion = React.useCallback(() => {
		setModal({
			component: ModalScoresByQuestionWithAPI,
			className: 'scoresByQuestion',
			props: {
				loID: instance.loID,
				instID: instance.instID
			}
		})
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
				<Button onClick={onClickAboutThisLO} type="text" text="About this learning object..." />
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
				<DefList
					items={[
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
					]}
				/>
				<Button onClick={onClickEditInstanceDetails} type="small" text="Edit Details" />
			</div>

			<SectionHeader label="Ownership &amp; Access" />
			<div className="ownership">
				<ul>
					{usersWithAccess.map(userItem => {
						return (
							<li key={userItem.userID}>{`${userItem.userString}${
								userItem.userID === user.userID ? ' (You)' : ''
							}`}</li>
						)
					})}
				</ul>
				<Button onClick={onClickManageAccess} type="small" text="Manage access" />
			</div>

			<AssessmentScoresSection
				instance={instance}
				setModal={setModal}
			/>

			<Button onClick={onClickViewScoresByQuestion} type="small" text="Compare Scores by question..." />

		</div>
	)
}

InstanceSection.propTypes = {
	instance: PropTypes.object,
	scores: PropTypes.array,
	onClickAddAdditionalAttempt: PropTypes.func.isRequired,
	onClickRemoveAdditionalAttempt: PropTypes.func.isRequired
}
