const Modal = ({ open, title, children }) => {
  if (!open) return null
  return (
    <div className="fixed inset-0 bg-black/30 p-4">
      <div className="mx-auto max-w-lg rounded-2xl bg-white p-6">
        <h2 className="mb-4 text-lg font-semibold">{title}</h2>
        {children}
      </div>
    </div>
  )
}
export default Modal
