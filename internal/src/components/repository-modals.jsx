import './repository-modals.scss'

import React, { useEffect } from 'react'
import ReactModal from 'react-modal'
import ModalAboutLO from './modal-about-lo'

const getModal = (modalType, modalProps) => {
	switch (modalType) {
		case 'aboutThisLO':
			return <ModalAboutLO {...modalProps} />
	}

	return null
}

const RepositoryModals = ({ modalType, modalProps, onCloseModal }) => {
	useEffect(() => {
		ReactModal.setAppElement('#repository')
	})

	const modal = getModal(modalType, modalProps)

	return modal ? (
		<ReactModal
			isOpen={true}
			contentLabel={'title'}
			className="repository--modal"
			overlayClassName="repository--modal-overlay"
			onRequestClose={onCloseModal}
		>
			{modal}
		</ReactModal>
	) : null
}

export default RepositoryModals
