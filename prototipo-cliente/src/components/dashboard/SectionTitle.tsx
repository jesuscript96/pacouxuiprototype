export function SectionTitle({ eyebrow }: { eyebrow: string }) {
  return (
    <div className="dash-section-title px-1">
      <span className="dash-section-eyebrow">{eyebrow}</span>
      <span className="dash-section-rule" />
    </div>
  )
}
