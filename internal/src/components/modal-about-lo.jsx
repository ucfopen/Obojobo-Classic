import React from 'react'
import Button from './button'
import DefList from './def-list'
import PropTypes from 'prop-types'
import FlashHTML from './flash-html'
import SectionHeader from './section-header'
import './modal-about-lo.scss'

export default function ModalAboutLO(props) {
	const items = [
		{ label: 'Learn Time', value: props.learnTime.toString() },
		{ label: 'Language', value: props.languageID == 1 ? 'English' : '' },
		{ label: 'Content Pages', value: props.contentSize.toString() },
		{ label: 'Practice Questions', value: props.practiceSize.toString() },
		{ label: 'Assessment Questions', value: props.assessmentSize.toString() },
		{ label: 'Author Notes', value: props.notes }
	]

	return (
		<div className="modal-about-learning-object">
			<SectionHeader label={'About this learning object'} />
			<DefList className="def-list" items={items} />
			<SectionHeader label={'Learning Objective'} />
			<div className="flash-html-container">
				<FlashHTML value={props.objective} />
			</div>
			<Button text="Close" type="text" onClick={props.onClose} />
		</div>
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
