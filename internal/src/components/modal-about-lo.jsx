import './modal-about-lo.scss'

import React from 'react'
import Button from './button'
import DefList from './def-list'
import PropTypes from 'prop-types'
import FlashHTML from './flash-html'
import SectionHeader from './section-header'
import { useQuery } from 'react-query'
import { apiGetLOMeta } from '../util/api'
import RepositoryModal from './repository-modal'

export function ModalAboutLOWithAPI({ instanceName, onClose, loID }) {
	const { isError, data, isFetching } = useQuery(['getLoMeta', loID], apiGetLOMeta, {
		initialStale: true,
		staleTime: Infinity
	})

	const props = React.useMemo(() => {
		if (isFetching || isError) return {}
		const { learnTime, languageID, notes, summary, objective, title } = data
		const { contentSize, practiceSize, assessmentSize } = summary
		return {
			learnTime,
			languageID,
			contentSize,
			practiceSize,
			assessmentSize,
			notes,
			objective,
			title
		}
	}, [onClose, loID, data, isFetching])

	if (isFetching) return null
	if (isError) return <div>Error Loading Data</div>
	return <ModalAboutLO instanceName={instanceName} {...props} onClose={onClose} />
}

export default function ModalAboutLO(props) {
	const items = [
		{ label: 'Title', value: props.title },
		{ label: 'Learn Time', value: props.learnTime.toString() },
		{ label: 'Language', value: props.languageID === 1 ? 'English' : '' },
		{ label: 'Content Pages', value: props.contentSize.toString() },
		{ label: 'Practice Questions', value: props.practiceSize.toString() },
		{ label: 'Assessment Questions', value: props.assessmentSize.toString() },
		{ label: 'Author Notes', value: props.notes }
	]

	return (
		<RepositoryModal
			className="aboutThisLO"
			instanceName={props.instanceName}
			onCloseModal={props.onClose}
		>
			<div className="modal-about-learning-object">
				<SectionHeader label={'About this learning object'} />
				<DefList className="def-list" items={items} />
				<SectionHeader label={'Learning Objective'} />
				<div className="flash-html-container">
					<FlashHTML value={props.objective} />
				</div>
				<Button text="Close" type="text" onClick={props.onClose} />
			</div>
		</RepositoryModal>
	)
}

ModalAboutLO.propTypes = {
	onClose: PropTypes.func.isRequired,
	learnTime: PropTypes.number.isRequired,
	languageID: PropTypes.number.isRequired,
	contentSize: PropTypes.string.isRequired,
	practiceSize: PropTypes.string.isRequired,
	assessmentSize: PropTypes.string.isRequired,
	notes: PropTypes.string.isRequired,
	objective: PropTypes.string.isRequired
}
