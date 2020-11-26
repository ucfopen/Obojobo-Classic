import './repository-modals.scss'

import React, { useEffect } from 'react'
import ReactDom from 'react-dom'
import ReactModal from 'react-modal'
import ModalAboutLO from './modal-about-lo'
import Button from './button'
import ModalInstanceDetails from './modal-instance-details'
import ModalScoreDetails from './modal-score-details'
import ModalScoresByQuestion from './modal-scores-by-question'
import ModalAboutObojoboNext from './modal-about-obojobo-next'
import { useQuery, useQueryCache } from 'react-query'
import { apiGetLOMeta } from '../util/api'


const ModalAboutLOWithAPI = ({onClose, loID}) => {
	const { isError, data, isFetching } = useQuery(['getLoMeta', loID], apiGetLOMeta, {
		initialStale: true,
		staleTime: Infinity,
	})

	const props = React.useMemo(() => {
		if(isFetching) return {}
		const {learnTime, languageID, notes, summary, objective } = data
		const {contentSize, practiceSize, assessmentSize} = summary
		return {
			learnTime,
			languageID,
			contentSize,
			practiceSize,
			assessmentSize,
			notes,
			objective
		}
	}, [onClose, loID, data, isFetching])

	if(isFetching) return null
	if(isError) return <div>Error Loading Data</div>
	return <ModalAboutLO {...props} onClose={onClose} />
}

const getModal = (modalType, modalProps, onCloseModal) => {
	switch (modalType) {
		case 'aboutThisLO':
			return <ModalAboutLOWithAPI onClose={onCloseModal} loID={modalProps.loID} />

		case 'instanceDetails':
			return <ModalInstanceDetails {...modalProps} onClose={onCloseModal} />

		case 'scoreDetails':
			return <ModalScoreDetails {...modalProps} onClose={onCloseModal} />

		case 'scoresByQuestion':
			return <ModalScoresByQuestion {...modalProps} onClose={onCloseModal} />

		case 'aboutObojoboNext':
			return <ModalAboutObojoboNext {...modalProps} onClose={onCloseModal} />

		default:
			return null
	}

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
			className={`repository--modal ${modalType}`}
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

ReactModal.setAppElement('#react-app');

export default RepositoryModals
