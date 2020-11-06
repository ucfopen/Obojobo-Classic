import './repository-page.scss'

import React, { useState, useCallback, useEffect } from 'react'
import { useQuery, useQueryCache } from 'react-query'
import { apiGetInstances } from '../util/api'
import MyInstances from './my-instances'
import InstanceSection from './instance-section'
import Header from './header'
import RepositoryModals from './repository-modals'

const RepositoryPage = () => {
	const queryCache = useQueryCache()
	const reloadInstances = useCallback(() => {
		queryCache.invalidateQueries('getInstances')
		setSelectedInstanceIndex(null)
	}, null)
	const { isError, data, error } = useQuery('getInstances', apiGetInstances, {
		initialStale: true,
		staleTime: 0
	})
	const [selectedInstanceIndex, setSelectedInstanceIndex] = useState(null)
	const [modal, setModal] = useState(null)

	if (isError) return <span>Error: {error.message}</span>

	const onClickAboutThisLO = () => {
		setModal({
			type: 'aboutThisLO',
			props: {
				learnTime: 20,
				language: 'English',
				numContentPages: 11,
				numPracticeQuestions: 13,
				numAssessmentQuestions: 10,
				authorNotes:
					'\rStudents will be able to identify the causes of plagiarism and how to avoid plagiarism.',
				learningObjective:
					'<TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0"></FONT></P></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="1">Given examples that include the following, students will be able to identify what constitutes plagiarism in their academic work and how to avoid the common causes of plagiarism when they use:<FONT KERNING="0"></FONT></FONT></P></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0"></FONT></P></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><LI><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0">direct quotes,</FONT></LI></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><LI><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0">paraphrased text,</FONT></LI></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><LI><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0">or summarized text.</FONT></LI></TEXTFORMAT><TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0"></FONT></P></TEXTFORMAT><TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0"></FONT></P></TEXTFORMAT>'
			}
		})
		// alert('onClickAboutThisLO')
	}

	const onClickEditInstanceDetails = () => {
		alert('onClickEditInstanceDetails')
	}

	const onClickManageAccess = () => {
		alert('onClickManageAccess')
	}

	const onClickDownloadScores = () => {
		alert('onClickDownloadScores')
	}

	const onClickViewScoresByQuestion = () => {
		alert('onClickViewScoresByQuestion')
	}

	const onClickHeaderAboutOrBannerLink = () => {
		alert('onClickHeaderAboutOrBannerLink')
	}

	const onClickHeaderCloseBanner = () => {
		alert('onClickHeaderCloseBanner')
	}

	const onClickLogOut = () => {
		alert('onClickLogOut')
	}

	const onClickPreview = () => {
		if (selectedInstanceIndex === null) {
			return
		}

		const selectedInstance = data[selectedInstanceIndex]

		window.open(`/preview/${selectedInstance.loID}`, '_blank')
	}

	return (
		<div id="repository" className="repository">
			<Header
				onClickAboutOrBannerLink={onClickHeaderAboutOrBannerLink}
				onClickCloseBanner={onClickHeaderCloseBanner}
				onClickLogOut={onClickLogOut}
			/>
			<main>
				<div className="wrapper">
					<MyInstances
						instances={data}
						selectedInstanceIndex={selectedInstanceIndex}
						onSelect={row => setSelectedInstanceIndex(row)}
						onClickRefresh={() => reloadInstances()}
					/>
					<InstanceSection
						onClickAboutThisLO={onClickAboutThisLO}
						onClickPreview={onClickPreview}
						onClickEditInstanceDetails={onClickEditInstanceDetails}
						onClickManageAccess={onClickManageAccess}
						onClickDownloadScores={onClickDownloadScores}
						onClickViewScoresByQuestion={onClickViewScoresByQuestion}
						instance={selectedInstanceIndex !== null ? data[selectedInstanceIndex] : null}
					/>
				</div>
			</main>
			{modal ? (
				<RepositoryModals
					modalType={modal.type}
					modalProps={modal.props}
					onCloseModal={() => setModal(null)}
				/>
			) : null}
		</div>
	)
}

export default RepositoryPage
