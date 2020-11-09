import './repository-modals.scss'

import React, { useEffect } from 'react'
import ReactModal from 'react-modal'
import ModalAboutLO from './modal-about-lo'
import Button from './button'
import ModalInstanceDetails from './modal-instance-details'
import ModalScoreDetails from './modal-score-details'
import ModalScoresByQuestion from './modal-scores-by-question'

const getModal = (modalType, modalProps, onCloseModal) => {
	switch (modalType) {
		case 'aboutThisLO':
			return <ModalAboutLO {...modalProps} onClose={onCloseModal} />

		case 'instanceDetails':
			return <ModalInstanceDetails {...modalProps} onClose={onCloseModal} />

		case 'scoreDetails':
			return <ModalScoreDetails {...modalProps} onClose={onCloseModal} />

		case 'scoresByQuestion':
			return <ModalScoresByQuestion {...modalProps} onClose={onCloseModal} />
	}

	return null
}

const RepositoryModals = ({ instanceName, modalType, modalProps, onCloseModal }) => {
	useEffect(() => {
		ReactModal.setAppElement('#repository')
	})

	const modal = getModal(modalType, modalProps, onCloseModal)

	return modal ? (
		<ReactModal
			isOpen={true}
			contentLabel={instanceName}
			className="repository--modal"
			overlayClassName="repository--modal-overlay"
			onRequestClose={onCloseModal}
		>
			<div>
				<div className="top-bar">
					<div className="module-title">{instanceName}</div>
					<Button type="text" ariaLabel="Close" onClick={onCloseModal} text="×" />
				</div>
				<div className="modal-content">{modal}</div>
			</div>
		</ReactModal>
	) : null
}

export default RepositoryModals
